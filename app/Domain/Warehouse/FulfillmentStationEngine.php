<?php

declare(strict_types=1);

namespace App\Domain\Warehouse;

use App\Models\SalesOrder;
use App\Models\PickingTask;
use App\Models\PickingItem;
use App\Models\WarehouseException;
use App\Models\CrmActivity;
use App\Domain\Transport\TransportManagementEngine;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class FulfillmentStationEngine
{
    public function __construct(
        protected TransportManagementEngine $transportEngine
    ) {}

    /**
     * Lock Task to the active warehouse operator.
     */
    public function lockTask(PickingTask $task, int $operatorId): PickingTask
    {
        if ($task->assigned_user_id && $task->assigned_user_id !== $operatorId && $task->status === 'picking') {
            return $task;
        }

        $task->update([
            'assigned_user_id' => $operatorId,
            'status' => $task->status === 'pending' ? 'picking' : $task->status,
            'started_at' => $task->started_at ?? now(),
        ]);

        return $task;
    }

    /**
     * Verify Item via Barcode / SKU scanning with duplicate scan prevention.
     */
    public function verifyBarcode(PickingTask $task, string $barcodeOrSku, int $operatorId): array
    {
        $term = trim($barcodeOrSku);
        $task->load('items.product');

        $itemToVerify = null;
        foreach ($task->items as $item) {
            $product = $item->product;
            if ($product) {
                if ($product->sku === $term || $product->barcode === $term || $product->code === $term || strtolower($product->name) === strtolower($term)) {
                    $itemToVerify = $item;
                    break;
                }
            }
        }

        if (!$itemToVerify) {
            throw new InvalidArgumentException("Barcode or SKU '{$term}' does not match any item in Task #{$task->task_number}.");
        }

        if ($itemToVerify->is_verified) {
            return [
                'success' => true,
                'already_verified' => true,
                'message' => "Item '{$itemToVerify->product->name}' is already verified.",
                'item_id' => $itemToVerify->id,
            ];
        }

        $itemToVerify->update([
            'is_verified' => true,
            'picked_quantity' => $itemToVerify->requested_quantity,
            'verified_by' => $operatorId,
            'verified_at' => now(),
        ]);

        $task->refresh();
        $totalItems = $task->items->count();
        $verifiedCount = $task->items->where('is_verified', true)->count();
        $progressPct = round(($verifiedCount / max(1, $totalItems)) * 100);

        return [
            'success' => true,
            'already_verified' => false,
            'message' => "Successfully verified '{$itemToVerify->product->name}' (SKU: {$itemToVerify->product->sku})!",
            'item_id' => $itemToVerify->id,
            'verified_count' => $verifiedCount,
            'total_items' => $totalItems,
            'progress_pct' => $progressPct,
            'all_verified' => ($verifiedCount === $totalItems),
        ];
    }

    /**
     * Short Pick & Exception Handling with Financial Threshold Rule (₹10,000 cutoff).
     */
    public function reportException(PickingTask $task, int $itemId, string $reason, int $actualQty, string $notes, int $operatorId): array
    {
        return DB::transaction(function () use ($task, $itemId, $reason, $actualQty, $notes, $operatorId) {
            $item = PickingItem::with('product')->where('picking_task_id', $task->id)->findOrFail($itemId);
            $product = $item->product;

            $requestedQty = (int)$item->requested_quantity;
            $actualQty = max(0, min($requestedQty, $actualQty));
            $missingQty = $requestedQty - $actualQty;

            $unitCost = (float)($product->cost_price > 0 ? $product->cost_price : $product->selling_price);
            $financialLoss = round($missingQty * $unitCost, 2);

            $excNum = 'EXC-' . date('Y') . '-' . str_pad((string)(WarehouseException::max('id') + 1), 5, '0', STR_PAD_LEFT);

            // Log Warehouse Exception
            $exception = WarehouseException::create([
                'exception_number' => $excNum,
                'task_reference' => $task->task_number,
                'order_reference' => $task->order_reference,
                'product_id' => $product->id,
                'exception_type' => strtolower(str_replace(' ', '_', $reason)),
                'severity' => $financialLoss > 10000 ? 'critical' : 'warning',
                'description' => "Short Pick Reported: Expected {$requestedQty}, Actual {$actualQty}. Missing {$missingQty} units. Loss: ₹{$financialLoss}. Notes: {$notes}",
                'status' => $financialLoss > 10000 ? 'open' : 'resolved',
                'reported_by' => $operatorId,
            ]);

            if ($financialLoss > 10000.00) {
                $task->update(['status' => 'manager_approval_required']);

                Log::warning("FulfillmentStationEngine: Financial Loss ₹{$financialLoss} > ₹10,000 on Task #{$task->task_number}. Manager Approval Required.");

                return [
                    'escalated' => true,
                    'financial_loss' => $financialLoss,
                    'message' => "Short pick financial loss (₹" . number_format($financialLoss, 2) . ") exceeds ₹10,000 threshold. Task escalated for Manager Approval.",
                ];
            } else {
                if ($missingQty > 0) {
                    $product->decrement('physical_stock', $missingQty);
                    if ($product->reserved_stock >= $missingQty) {
                        $product->decrement('reserved_stock', $missingQty);
                    }
                }

                $item->update([
                    'requested_quantity' => $actualQty,
                    'picked_quantity' => $actualQty,
                    'is_verified' => true,
                    'verified_by' => $operatorId,
                    'verified_at' => now(),
                ]);

                Log::info("FulfillmentStationEngine: Auto-adjusted ₹{$financialLoss} short pick for Task #{$task->task_number}.");

                return [
                    'escalated' => false,
                    'financial_loss' => $financialLoss,
                    'message' => "Short pick processed & inventory auto-adjusted (Loss: ₹" . number_format($financialLoss, 2) . ").",
                ];
            }
        });
    }

    /**
     * Final Warehouse Action: "Seal Package & Ready for Dispatch"
     * Decrements physical & reserved stock, updates Sales Order to ready_for_dispatch,
     * completes Warehouse Task, and transitions Transport Request status to ready_for_dispatch.
     */
    public function sealAndMarkReadyForDispatch(PickingTask $task, array $packageInfo, int $operatorId): SalesOrder
    {
        return DB::transaction(function () use ($task, $packageInfo, $operatorId) {
            $task->load(['items.product', 'warehouse']);
            $order = SalesOrder::where('order_number', $task->order_reference)->firstOrFail();

            if ($task->status === 'completed' || $order->status === 'ready_for_dispatch' || $order->status === 'dispatched') {
                throw new InvalidArgumentException("Task #{$task->task_number} has already been sealed & completed.");
            }

            // Zero Defect Guard: Ensure all items verified
            if (!$task->is_all_verified) {
                throw new InvalidArgumentException("Cannot seal package until ALL items on the fulfillment checklist are verified!");
            }

            // 1. Decrement Physical & Reserved Stock on Products Table
            foreach ($task->items as $item) {
                if ($item->product) {
                    $product = $item->product;
                    $qty = (int)$item->requested_quantity;

                    $product->decrement('physical_stock', $qty);
                    if ($product->reserved_stock >= $qty) {
                        $product->decrement('reserved_stock', $qty);
                    }
                }
            }

            // 2. Complete Warehouse Task & Update Sales Order to Ready For Dispatch
            $task->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            $packageType = $packageInfo['package_type'] ?? 'Carton';
            $weight = (float)($packageInfo['weight_kg'] ?? 2.5);

            $order->update([
                'status' => 'ready_for_dispatch',
                'internal_notes' => ($order->internal_notes ? $order->internal_notes . "\n" : '') . "Sealed & Marked Ready for Dispatch ({$packageType}, {$weight}kg) on " . now()->format('Y-m-d H:i:s'),
            ]);

            // 3. Trigger Domain Handoff to Transport Portal (Updates Transport Request status -> ready_for_dispatch)
            $this->transportEngine->markReadyForDispatch($order->order_number, [
                'package_type' => $packageType,
                'weight_kg' => $weight,
                'package_count' => $packageInfo['package_count'] ?? 1,
            ]);

            // 4. Log Customer Timeline
            CrmActivity::create([
                'customer_id' => $order->customer_id,
                'activity_type' => 'note',
                'subject' => "Order #{$order->order_number} Sealed & Ready for Dispatch",
                'description' => "Warehouse completed packaging ({$packageType}, {$weight}kg). Physical stock decremented. Order handed off to Transport Department.",
                'activity_date' => now(),
                'user_id' => $operatorId,
            ]);

            Log::info("FulfillmentStationEngine: Sealed Package & Marked Order #{$order->order_number} READY_FOR_DISPATCH for Transport Dept.");

            return $order;
        });
    }
}

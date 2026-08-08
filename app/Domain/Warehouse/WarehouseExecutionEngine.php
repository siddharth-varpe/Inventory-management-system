<?php

declare(strict_types=1);

namespace App\Domain\Warehouse;

use App\Models\SalesOrder;
use App\Models\PickingTask;
use App\Models\PickingItem;
use App\Models\DispatchTask;
use App\Models\CrmActivity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;

class WarehouseExecutionEngine
{
    /**
     * Atomically generate a live Warehouse Execution Task (PickingTask & Items) from a Sales Order.
     */
    public function createTaskFromSalesOrder(SalesOrder $order): PickingTask
    {
        return DB::transaction(function () use ($order) {
            $order->load(['customer', 'warehouse', 'items.product']);

            // Guard against duplicate task generation
            $existingTask = PickingTask::where('order_reference', $order->order_number)->first();
            if ($existingTask) {
                return $existingTask;
            }

            $nextId = PickingTask::max('id') + 1;
            $taskNumber = 'PICK-' . date('Y') . '-' . str_pad((string)$nextId, 5, '0', STR_PAD_LEFT);

            $task = PickingTask::create([
                'task_number' => $taskNumber,
                'order_reference' => $order->order_number,
                'customer_name' => $order->customer->company_name ?? 'B2B Customer',
                'picking_type' => 'single',
                'priority' => $order->order_priority ?? 'medium',
                'is_fragile' => false,
                'is_cold_chain' => false,
                'warehouse_id' => $order->warehouse_id ?? 1,
                'status' => 'pending',
            ]);

            foreach ($order->items as $item) {
                $reqQty = (int)($item->reserved_qty > 0 ? $item->reserved_qty : $item->ordered_qty);
                if ($reqQty > 0) {
                    $defaultBin = $item->product->sku ? "WH01-A01-R01-{$item->product->sku}" : "WH01-MAIN-STORAGE";
                    
                    PickingItem::create([
                        'picking_task_id' => $task->id,
                        'product_id' => $item->product_id,
                        'location_coordinate' => $defaultBin,
                        'requested_quantity' => $reqQty,
                        'picked_quantity' => 0,
                        'is_verified' => false,
                    ]);
                }
            }

            // Log CRM Activity Event for Customer Timeline
            CrmActivity::create([
                'customer_id' => $order->customer_id,
                'activity_type' => 'note',
                'subject' => "Warehouse Task #{$task->task_number} Generated",
                'description' => "Sales Order #{$order->order_number} routed to Organize Stock Portal. Picking Task #{$task->task_number} created.",
                'activity_date' => now(),
                'user_id' => auth()->id() ?? 1,
            ]);

            Log::info("WarehouseExecutionEngine: Generated Task #{$task->task_number} for Order #{$order->order_number}");

            return $task;
        });
    }

    /**
     * Start Picking Process for a Task.
     */
    public function startPicking(PickingTask $task, int $operatorId): PickingTask
    {
        if (!in_array($task->status, ['pending', 'assigned'])) {
            throw new InvalidArgumentException("Task #{$task->task_number} cannot start picking from status '{$task->status}'.");
        }

        $task->update([
            'status' => 'picking',
            'assigned_user_id' => $operatorId,
            'started_at' => now(),
        ]);

        return $task;
    }

    /**
     * Complete Picking Process for a Task.
     */
    public function completePicking(PickingTask $task, int $operatorId): PickingTask
    {
        if ($task->status !== 'picking' && $task->status !== 'pending') {
            throw new InvalidArgumentException("Task #{$task->task_number} must be in 'picking' status to complete picking.");
        }

        return DB::transaction(function () use ($task, $operatorId) {
            foreach ($task->items as $item) {
                $item->update([
                    'is_verified' => true,
                    'picked_quantity' => $item->requested_quantity,
                    'verified_by' => $operatorId,
                    'verified_at' => now(),
                ]);
            }

            $task->update([
                'status' => 'picked',
                'assigned_user_id' => $operatorId,
                'completed_at' => now(),
            ]);

            // Automatically prepare linked Dispatch Task record in queued/pending_pack state
            $dispRef = 'DISP-' . date('Y') . '-' . str_pad((string)(DispatchTask::max('id') + 1), 5, '0', STR_PAD_LEFT);
            $totalItems = $task->items->sum('requested_quantity');

            DispatchTask::updateOrCreate(
                ['picking_task_id' => $task->id],
                [
                    'dispatch_number' => $dispRef,
                    'order_reference' => $task->order_reference,
                    'customer_name' => $task->customer_name,
                    'total_items' => $totalItems,
                    'total_weight_kg' => round($totalItems * 1.5, 2),
                    'shipping_label_code' => 'LBL-' . strtoupper(Str::random(8)),
                    'status' => 'queued',
                    'created_by' => $operatorId,
                ]
            );

            Log::info("WarehouseExecutionEngine: Picking completed for Task #{$task->task_number}. Moved to Packing Queue.");

            return $task;
        });
    }

    /**
     * Complete Packing Process for a Task.
     */
    public function completePacking(PickingTask $task, int $operatorId): PickingTask
    {
        if (!in_array($task->status, ['picked', 'picking'])) {
            throw new InvalidArgumentException("Task #{$task->task_number} is not ready for packing completion. Current status: '{$task->status}'.");
        }

        return DB::transaction(function () use ($task, $operatorId) {
            $task->update([
                'status' => 'packed',
            ]);

            if ($task->dispatchTask) {
                $task->dispatchTask->update([
                    'status' => 'packed',
                ]);
            }

            Log::info("WarehouseExecutionEngine: Packing completed for Task #{$task->task_number}. Moved to Dispatch Queue.");

            return $task;
        });
    }

    /**
     * Execute Final Goods Dispatch: Decrements Physical & Reserved Stock, Updates Sales Order & Customer Timeline.
     */
    public function dispatchGoods(PickingTask $task, array $transportDetails, int $operatorId): SalesOrder
    {
        if (!in_array($task->status, ['packed', 'picked', 'picking'])) {
            throw new InvalidArgumentException("Task #{$task->task_number} is not ready for dispatch. Current status: '{$task->status}'.");
        }

        return DB::transaction(function () use ($task, $transportDetails, $operatorId) {
            $order = SalesOrder::where('order_number', $task->order_reference)->firstOrFail();

            // Decrement Physical Stock AND Reserved Stock for all items
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

            // Update Task Status
            $task->update(['status' => 'dispatched']);
            if ($task->dispatchTask) {
                $task->dispatchTask->update([
                    'status' => 'dispatched',
                    'shipping_label_code' => $transportDetails['tracking_number'] ?? $task->dispatchTask->shipping_label_code,
                ]);
            }

            // Update Sales Order Status to Dispatched
            $carrier = $transportDetails['carrier'] ?? 'Standard Transport';
            $tracking = $transportDetails['tracking_number'] ?? ('TRK-' . strtoupper(Str::random(8)));
            
            $order->update([
                'status' => 'dispatched',
                'internal_notes' => ($order->internal_notes ? $order->internal_notes . "\n" : '') . "Dispatched via {$carrier} (Tracking: {$tracking}) on " . now()->format('Y-m-d H:i:s'),
            ]);

            // Append Customer Timeline
            CrmActivity::create([
                'customer_id' => $order->customer_id,
                'activity_type' => 'note',
                'subject' => "Sales Order #{$order->order_number} Dispatched",
                'description' => "Goods dispatched via {$carrier}. Tracking #: {$tracking}. Physical inventory updated.",
                'activity_date' => now(),
                'user_id' => $operatorId,
            ]);

            Log::info("WarehouseExecutionEngine: Goods Dispatched for Order #{$order->order_number}. Physical stock decremented.");

            return $order;
        });
    }
}

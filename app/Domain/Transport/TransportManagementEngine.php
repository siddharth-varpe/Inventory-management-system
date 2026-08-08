<?php

declare(strict_types=1);

namespace App\Domain\Transport;

use App\Models\SalesOrder;
use App\Models\TransportRequest;
use App\Models\PickingTask;
use App\Models\AuditLog;
use App\Models\CrmActivity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class TransportManagementEngine
{
    /**
     * Handover from Warehouse: Automatically receive sealed warehouse order into Transport Intake.
     * Entry Conditions:
     * - Pick & Pack completed
     * - Warehouse verification completed
     * - Package sealed ("Seal & Ready to Dispatch")
     */
    public function createTransportTaskFromSealedPickingTask(PickingTask $pickingTask): TransportRequest
    {
        // Entry Condition Check
        if (!$pickingTask->is_all_verified) {
            Log::warning("TransportIntake: Refused Transport Task for Task #{$pickingTask->task_number}. Warehouse checklist verification incomplete.");
            throw new InvalidArgumentException("Incomplete warehouse verification. All items must be verified before transport intake.");
        }

        if (!in_array($pickingTask->status, ['seal_ready', 'completed'])) {
            Log::warning("TransportIntake: Refused Transport Task for Task #{$pickingTask->task_number}. Order status must be 'Seal & Ready to Dispatch'. Current status: {$pickingTask->status}");
            throw new InvalidArgumentException("Order status must be 'Seal & Ready to Dispatch' to enter Transport Intake.");
        }

        // Duplicate Check (One-to-One relationship enforced)
        $existing = TransportRequest::where('order_reference', $pickingTask->order_reference)->first();
        if ($existing) {
            Log::info("TransportIntake: Order #{$pickingTask->order_reference} already in Transport Queue as Task #{$existing->request_number}");
            return $existing;
        }

        // Find Enterprise Sales Order
        $order = SalesOrder::with('customer')->where('order_number', $pickingTask->order_reference)->first();
        if (!$order) {
            Log::error("TransportIntake Failure: Missing enterprise order reference '{$pickingTask->order_reference}' in Sales database.");
            throw new InvalidArgumentException("Missing enterprise order reference {$pickingTask->order_reference}.");
        }

        // Generate Transport Task ID (e.g. TRN-2026-000154 from SO-2026-000154)
        $digits = preg_replace('/[^0-9]/', '', $order->order_number);
        $orderSuffix = strlen($digits) >= 6 ? substr($digits, -6) : $digits;
        if (!empty($orderSuffix)) {
            $trnTaskId = 'TRN-' . date('Y') . '-' . str_pad($orderSuffix, 6, '0', STR_PAD_LEFT);
        } else {
            $trnTaskId = 'TRN-' . date('Y') . '-' . str_pad((string)(TransportRequest::max('id') + 1), 6, '0', STR_PAD_LEFT);
        }

        // Ensure Transport Task ID uniqueness
        $dupTrn = TransportRequest::where('request_number', $trnTaskId)->exists();
        if ($dupTrn) {
            $trnTaskId = 'TRN-' . date('Ymd') . '-' . str_pad((string)(TransportRequest::max('id') + 1), 4, '0', STR_PAD_LEFT);
        }

        // Parse City from delivery address
        $deliveryAddr = $order->delivery_address ?? 'Central City Address, Mumbai';
        $addrParts = explode(',', $deliveryAddr);
        $city = trim(end($addrParts));
        if (empty($city) || strlen($city) < 3) {
            $city = $order->customer->city ?? 'Mumbai';
        }

        // Compute Package Details
        $pkgCount = $pickingTask->items->count() ?: 1;
        $totalQty = $pickingTask->items->sum('requested_quantity') ?: 1;
        $totalWeight = $totalQty * 1.5;

        // Create Transport Intake Task
        $request = TransportRequest::create([
            'request_number' => $trnTaskId,
            'sales_order_id' => $order->id,
            'order_reference' => $order->order_number,
            'customer_name' => $order->customer->company_name ?? $pickingTask->customer_name ?? 'B2B Customer',
            'delivery_address' => $deliveryAddr,
            'delivery_city' => $city,
            'contact_person' => $order->contact_person ?? 'Logistics Manager',
            'phone_number' => $order->phone_number ?? 'N/A',
            'priority' => $pickingTask->priority ?? $order->order_priority ?? 'normal',
            'expected_delivery_date' => $order->delivery_date ?? now()->addDays(2),
            'required_dispatch_date' => $order->expected_dispatch_date ?? now()->addDays(1),
            'package_count' => $pkgCount,
            'package_type' => 'Sealed Carton',
            'weight_kg' => $totalWeight,
            'dimensions' => '40x30x20 cm',
            'warehouse_completed_at' => $pickingTask->completed_at ?? now(),
            'source_module' => 'Pick & Pack Station',
            'status' => 'waiting_planning',
            'created_by' => auth()->id() ?? 1,
        ]);

        // Record Immutable Audit Log
        AuditLog::create([
            'user_id' => auth()->id() ?? 1,
            'module' => 'Transport Department',
            'action' => 'Transport Task Created',
            'table_name' => 'transport_requests',
            'record_id' => $request->id,
            'old_values' => null,
            'new_values' => json_encode([
                'transport_task_id' => $request->request_number,
                'enterprise_order_id' => $request->order_reference,
                'source_module' => 'Pick & Pack Station',
                'warehouse_operator' => auth()->user()->name ?? 'System Warehouse Operator',
                'timestamp' => now()->toIso8601String(),
                'status' => 'Waiting for Transport Planning',
            ]),
            'ip_address' => request()->ip() ?? '127.0.0.1',
            'user_agent' => request()->userAgent() ?? 'System Integration Engine',
        ]);

        Log::info("TransportIntake SUCCESS: Automated Handover complete. Enterprise Order #{$order->order_number} -> Transport Task #{$request->request_number} (Waiting for Transport Planning)");

        return $request;
    }

    /**
     * Create or synchronize Transport Task from Sales Order upon creation in CRM.
     * Initial Status: AWAITING WAREHOUSE (locked until Pick & Pack completion).
     */
    public function createTransportRequestFromSalesOrder(SalesOrder $order): TransportRequest
    {
        return DB::transaction(function () use ($order) {
            $existing = TransportRequest::where('sales_order_id', $order->id)
                ->orWhere('order_reference', $order->order_number)
                ->first();

            if ($existing) {
                return $existing;
            }

            $order->load(['customer']);

            // Generate Transport Task ID (e.g. TRN-2026-000154 from SO-2026-000154)
            $digits = preg_replace('/[^0-9]/', '', $order->order_number);
            $orderSuffix = strlen($digits) >= 6 ? substr($digits, -6) : $digits;
            if (!empty($orderSuffix)) {
                $trnTaskId = 'TRN-' . date('Y') . '-' . str_pad($orderSuffix, 6, '0', STR_PAD_LEFT);
            } else {
                $trnTaskId = 'TRN-' . date('Y') . '-' . str_pad((string)(TransportRequest::max('id') + 1), 6, '0', STR_PAD_LEFT);
            }

            if (TransportRequest::where('request_number', $trnTaskId)->exists()) {
                $trnTaskId = 'TRN-' . date('Ymd') . '-' . str_pad((string)(TransportRequest::max('id') + 1), 4, '0', STR_PAD_LEFT);
            }

            $deliveryAddr = $order->delivery_address ?? 'Primary Customer Address, Mumbai';
            $addrParts = explode(',', $deliveryAddr);
            $city = trim(end($addrParts));
            if (empty($city) || strlen($city) < 3) {
                $city = $order->customer->city ?? 'Mumbai';
            }

            $request = TransportRequest::create([
                'request_number' => $trnTaskId,
                'sales_order_id' => $order->id,
                'order_reference' => $order->order_number,
                'customer_name' => $order->customer->company_name ?? $order->customer_name ?? 'B2B Customer',
                'delivery_address' => $deliveryAddr,
                'delivery_city' => $city,
                'contact_person' => $order->contact_person ?? 'Logistics Manager',
                'phone_number' => $order->phone_number ?? $order->customer->phone ?? 'N/A',
                'priority' => $order->order_priority ?? $order->priority ?? 'normal',
                'expected_delivery_date' => $order->delivery_date ?? now()->addDays(2),
                'required_dispatch_date' => $order->expected_dispatch_date ?? now()->addDays(1),
                'package_count' => 1,
                'package_type' => 'Standard Package',
                'weight_kg' => 2.5,
                'dimensions' => '40x30x20 cm',
                'source_module' => 'CRM Sales Order',
                'warehouse_status' => 'picking_in_progress',
                'status' => 'awaiting_warehouse',
                'created_by' => $order->created_by ?? auth()->id() ?? 1,
            ]);

            AuditLog::create([
                'user_id' => auth()->id() ?? 1,
                'module' => 'Transport Department',
                'action' => 'Transport Task Created',
                'table_name' => 'transport_requests',
                'record_id' => $request->id,
                'old_values' => null,
                'new_values' => json_encode([
                    'transport_task_id' => $request->request_number,
                    'enterprise_order_id' => $request->order_reference,
                    'status' => 'AWAITING WAREHOUSE',
                    'source_module' => 'CRM Sales Order',
                    'timestamp' => now()->toIso8601String(),
                ]),
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'Sales Order Sync Engine',
            ]);

            Log::info("TransportSync: Created Transport Task #{$request->request_number} for Order #{$order->order_number} in AWAITING WAREHOUSE state.");

            return $request;
        });
    }

    /**
     * Automated Warehouse Synchronization: Triggered when Organize Stock seals package & marks ready for dispatch.
     * Automatically transitions Transport Status: AWAITING WAREHOUSE -> READY FOR ASSIGNMENT.
     */
    public function markReadyForDispatch(string $orderReference, array $packageInfo = []): ?TransportRequest
    {
        return DB::transaction(function () use ($orderReference, $packageInfo) {
            $task = TransportRequest::where('order_reference', $orderReference)
                ->lockForUpdate()
                ->first();

            if (!$task) {
                $order = SalesOrder::where('order_number', $orderReference)->first();
                if ($order) {
                    $task = $this->createTransportRequestFromSalesOrder($order);
                }
            }

            if ($task) {
                $oldStatus = $task->status;

                $updateData = [
                    'status' => 'ready_for_assignment',
                    'warehouse_status' => 'seal_ready',
                    'warehouse_completed_at' => now(),
                ];

                if (!empty($packageInfo['package_count'])) {
                    $updateData['package_count'] = (int)$packageInfo['package_count'];
                }
                if (!empty($packageInfo['package_type'])) {
                    $updateData['package_type'] = $packageInfo['package_type'];
                }
                if (!empty($packageInfo['weight_kg'])) {
                    $updateData['weight_kg'] = (float)$packageInfo['weight_kg'];
                }

                $task->update($updateData);

                AuditLog::create([
                    'user_id' => auth()->id() ?? 1,
                    'module' => 'Transport Department',
                    'action' => 'Ready for Assignment',
                    'table_name' => 'transport_requests',
                    'record_id' => $task->id,
                    'old_values' => json_encode(['status' => $oldStatus]),
                    'new_values' => json_encode([
                        'transport_task_id' => $task->request_number,
                        'enterprise_order_id' => $task->order_reference,
                        'previous_status' => $oldStatus,
                        'new_status' => 'READY FOR ASSIGNMENT',
                        'warehouse_completed_at' => $task->warehouse_completed_at->toIso8601String(),
                        'source_module' => 'Organize Stock',
                        'timestamp' => now()->toIso8601String(),
                    ]),
                    'ip_address' => request()->ip() ?? '127.0.0.1',
                    'user_agent' => request()->userAgent() ?? 'Warehouse Sync Engine',
                ]);

                Log::info("TransportSync: Task #{$task->request_number} (Order #{$task->order_reference}) automatically transitioned from AWAITING WAREHOUSE to READY FOR ASSIGNMENT.");
            }

            return $task;
        });
    }

    /**
     * Alias for warehouse completion synchronization
     */
    public function syncWarehouseCompletion(PickingTask $pickingTask): ?TransportRequest
    {
        return $this->markReadyForDispatch($pickingTask->order_reference, [
            'package_count' => $pickingTask->items()->count() ?: 1,
            'package_type' => 'Sealed Carton',
            'weight_kg' => ($pickingTask->items()->sum('requested_quantity') ?: 1) * 1.5,
        ]);
    }

    /**
     * Synchronize Sales Order cancellation from CRM.
     */
    public function syncOrderCancellation(SalesOrder $order, string $reason = 'Order Cancelled in CRM'): ?TransportRequest
    {
        return DB::transaction(function () use ($order, $reason) {
            $task = TransportRequest::where('sales_order_id', $order->id)
                ->orWhere('order_reference', $order->order_number)
                ->lockForUpdate()
                ->first();

            if ($task) {
                $oldStatus = $task->status;
                $task->update([
                    'status' => 'cancelled',
                    'delivery_failure_reason' => "Order Cancelled: {$reason}",
                ]);

                AuditLog::create([
                    'user_id' => auth()->id() ?? 1,
                    'module' => 'Transport Department',
                    'action' => 'Transport Task Cancelled',
                    'table_name' => 'transport_requests',
                    'record_id' => $task->id,
                    'old_values' => json_encode(['status' => $oldStatus]),
                    'new_values' => json_encode([
                        'transport_task_id' => $task->request_number,
                        'enterprise_order_id' => $task->order_reference,
                        'previous_status' => $oldStatus,
                        'new_status' => 'CANCELLED',
                        'reason' => $reason,
                        'timestamp' => now()->toIso8601String(),
                    ]),
                    'ip_address' => request()->ip() ?? '127.0.0.1',
                    'user_agent' => request()->userAgent() ?? 'Sales Cancel Sync Engine',
                ]);

                Log::info("TransportSync: Task #{$task->request_number} (Order #{$task->order_reference}) marked CANCELLED due to CRM cancellation.");
            }

            return $task;
        });
    }
}


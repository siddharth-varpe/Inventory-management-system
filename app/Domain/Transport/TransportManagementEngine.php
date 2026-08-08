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
     * Legacy helper for backward compatibility
     */
    public function createTransportRequestFromSalesOrder(SalesOrder $order): TransportRequest
    {
        $existing = TransportRequest::where('sales_order_id', $order->id)->first();
        if ($existing) {
            return $existing;
        }

        $trpNum = 'TRN-' . date('Y') . '-' . str_pad((string)(TransportRequest::max('id') + 1), 6, '0', STR_PAD_LEFT);

        return TransportRequest::create([
            'request_number' => $trpNum,
            'sales_order_id' => $order->id,
            'order_reference' => $order->order_number,
            'customer_name' => $order->customer->company_name ?? $order->customer_name ?? 'B2B Customer',
            'delivery_address' => $order->delivery_address ?? 'Primary Customer Address',
            'delivery_city' => 'Mumbai',
            'priority' => $order->priority ?? 'normal',
            'expected_delivery_date' => $order->delivery_date ?? now()->addDays(2),
            'status' => 'waiting_planning',
            'created_by' => $order->created_by ?? 1,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\Sales;

use App\Models\SalesOrder;
use App\Models\Quotation;
use App\Models\Customer;
use App\Models\Warehouse;
use App\Domain\Transport\TransportManagementEngine;
use Illuminate\Support\Facades\DB;

class SalesOrderService
{
    public function __construct(
        protected ReservationEngine $reservationEngine,
        protected SendGoodsConnector $sendGoodsConnector,
        protected QuotationService $quotationService,
        protected TransportManagementEngine $transportEngine
    ) {}

    /**
     * Create Sales Order from an Approved Quotation with strict Conversion Eligibility guards.
     */
    public function createFromQuotation(Quotation $quotation, int $userId, ?int $warehouseId = null): SalesOrder
    {
        return DB::transaction(function () use ($quotation, $userId, $warehouseId) {
            // Guard Check: Atomic validation with row-level locks on inventory products
            $this->quotationService->validateConversionEligibility($quotation, true);

            $customer = $quotation->customer;
            $warehouse = $warehouseId ? Warehouse::find($warehouseId) : Warehouse::first();

            $nextId = SalesOrder::max('id') + 1;
            $orderNumber = 'SO-' . date('Y') . '-' . str_pad((string)$nextId, 5, '0', STR_PAD_LEFT);

            $order = SalesOrder::create([
                'order_number' => $orderNumber,
                'quotation_id' => $quotation->id,
                'customer_id' => $customer->id,
                'salesperson_id' => $quotation->salesperson_id,
                'warehouse_id' => $warehouse->id ?? 1,
                'order_date' => date('Y-m-d'),
                'expected_dispatch_date' => date('Y-m-d', strtotime('+3 days')),
                'order_priority' => 'normal',
                'status' => 'approved',
                'subtotal' => $quotation->subtotal,
                'order_discount_amount' => $quotation->order_discount_amount,
                'taxable_amount' => $quotation->taxable_amount,
                'cgst_amount' => $quotation->cgst_amount,
                'sgst_amount' => $quotation->sgst_amount,
                'igst_amount' => $quotation->igst_amount,
                'grand_total' => $quotation->grand_total,
                'delivery_address' => $customer->addresses()->where('type', 'shipping')->first()?->address_line_1 ?? 'Primary Customer Address',
                'payment_terms' => $quotation->payment_terms,
                'remarks' => $quotation->remarks,
                'internal_notes' => null,
                'created_by' => $userId,
                'approved_by' => $userId,
                'approved_at' => now(),
            ]);

            foreach ($quotation->items as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'ordered_qty' => $item->quantity,
                    'reserved_qty' => 0,
                    'dispatched_qty' => 0,
                    'backorder_qty' => 0,
                    'unit_price' => $item->unit_price,
                    'discount_amount' => $item->discount_amount,
                    'taxable_value' => $item->taxable_value,
                    'gst_rate' => $item->gst_rate,
                    'gst_amount' => $item->gst_amount,
                    'line_total' => $item->line_total,
                ]);
            }

            // Update Quotation Status to Read-Only Converted State with Referential FK Linking
            $quotation->update([
                'status' => 'converted',
                'sales_order_id' => $order->id,
                'converted_at' => now(),
                'converted_by' => $userId,
                'internal_notes' => ($quotation->internal_notes ? $quotation->internal_notes . "\n" : '') . "Converted to Sales Order #{$order->order_number} on " . now()->format('Y-m-d H:i:s'),
            ]);

            // Auto Reserve Inventory & Emit Warehouse Pick Request to Send Goods Portal
            $this->reservationEngine->reserveInventory($order);
            $this->sendGoodsConnector->createDispatchRequest($order);
            $order->update(['status' => 'waiting_warehouse']);

            return $order;
        });
    }

    /**
     * Approve Sales Order and trigger Inventory Reservation & Warehouse Pick Request.
     */
    public function approveOrder(SalesOrder $order, int $approverId): SalesOrder
    {
        return DB::transaction(function () use ($order, $approverId) {
            $order->update([
                'status' => 'approved',
                'approved_by' => $approverId,
                'approved_at' => now(),
            ]);

            // Trigger Reservation Engine
            $order = $this->reservationEngine->reserveInventory($order);

            // Emit Send Goods Dispatch Request & advance status to waiting_warehouse
            $this->sendGoodsConnector->createDispatchRequest($order);
            $order->update(['status' => 'waiting_warehouse']);

            return $order;
        });
    }

    /**
     * Cancel Sales Order and release Inventory Reservation.
     */
    public function cancelOrder(SalesOrder $order, int $userId, string $reason): SalesOrder
    {
        return DB::transaction(function () use ($order, $reason) {
            // Release Inventory Reservation
            $this->reservationEngine->releaseReservation($order);

            $order->update([
                'status' => 'cancelled',
                'internal_notes' => ($order->internal_notes ? $order->internal_notes . "\n" : '') . "Cancellation Reason: " . $reason,
            ]);

            // Synchronize Order Cancellation to Transport Department
            $this->transportEngine->syncOrderCancellation($order, $reason);

            return $order;
        });
    }
}

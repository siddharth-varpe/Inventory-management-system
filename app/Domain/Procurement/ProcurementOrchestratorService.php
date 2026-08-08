<?php

declare(strict_types=1);

namespace App\Domain\Procurement;

use App\Domain\Events\GoodsReceiptCompleted;
use App\Domain\Events\InvoiceMatched;
use App\Domain\Events\PurchaseOrderApproved;
use App\Domain\Events\PurchaseRequisitionApproved;
use App\Domain\Events\PurchaseRequisitionCreated;
use App\Domain\Events\ShipmentArrived;
use App\Domain\Events\ShipmentDispatched;
use App\Domain\EventBus\EnterpriseEventBus;
use App\Jobs\Procurement\GenerateProcurementPdfJob;
use App\Jobs\Procurement\RecalculateVendorPerformanceJob;
use App\Jobs\Procurement\SendSupplierPoEmailJob;
use App\Models\GoodsReceiptNote;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequisition;
use App\Models\Supplier;
use App\Models\SupplierInvoice;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use DomainException;
use InvalidArgumentException;

class ProcurementOrchestratorService
{
    public function __construct(
        protected DocumentNumberGenerator $numGen,
        protected ApprovalEngine $approvalEngine,
        protected ProcurementMathEngine $mathEngine,
        protected ThreeWayMatchEngine $matchEngine,
        protected LandedCostEngine $landedEngine,
        protected EnterpriseEventBus $eventBus
    ) {}

    /**
     * Create Purchase Requisition
     */
    public function createRequisition(int $requestedBy, ?int $departmentId, string $priority, string $purpose, array $items): PurchaseRequisition
    {
        $prNumber = $this->numGen->generate('PR', 'purchase_requisitions', 'requisition_number');

        $pr = PurchaseRequisition::create([
            'requisition_number' => $prNumber,
            'department_id' => $departmentId,
            'requested_by' => $requestedBy,
            'priority' => $priority,
            'status' => 'pending_approval',
            'purpose' => $purpose,
        ]);

        foreach ($items as $item) {
            $product = Product::findOrFail($item['product_id']);
            $pr->items()->create([
                'product_id' => $product->id,
                'quantity_requested' => (int) $item['quantity'],
                'estimated_unit_cost' => (float) ($item['estimated_unit_cost'] ?? $product->cost_price),
                'status' => 'pending',
            ]);
        }

        $event = new PurchaseRequisitionCreated(
            module: 'OrderSupplies',
            payload: ['pr_id' => $pr->id, 'priority' => $priority],
            referenceNo: $pr->requisition_number,
            userId: $requestedBy,
            branchId: 1
        );
        $this->eventBus->dispatch($event->eventData);

        return $pr;
    }

    /**
     * Approve Purchase Requisition & Convert to PO / RFQ (Enterprise Approval Engine)
     */
    public function approveRequisition(int $prId, int $userId, ?string $mode = null): array
    {
        return DB::transaction(function () use ($prId, $userId, $mode) {
            $pr = PurchaseRequisition::with('items.product')->findOrFail($prId);

            if ($pr->status !== 'pending_approval') {
                throw new DomainException("Purchase Requisition {$pr->requisition_number} cannot be approved because its current status is {$pr->status}.");
            }

            $totalAmount = 0.0;
            foreach ($pr->items as $item) {
                $totalAmount += $item->quantity_requested * (float) $item->estimated_unit_cost;
            }

            $this->approvalEngine->evaluateApprovalLevel($totalAmount);

            $pr->update([
                'status' => 'approved',
                'approved_by' => $userId,
                'approved_at' => now(),
            ]);

            $effectiveMode = $mode ?? config('procurement.workflow_mode', 'DIRECT_PO');

            if ($effectiveMode === 'RFQ_REQUIRED') {
                $rfqNumber = $this->numGen->generate('RFQ', 'rfqs', 'rfq_number');
                $rfq = \App\Models\Rfq::create([
                    'rfq_number' => $rfqNumber,
                    'purchase_requisition_id' => $pr->id,
                    'title' => 'RFQ for Requisition ' . $pr->requisition_number,
                    'status' => 'draft',
                    'created_by' => $userId,
                ]);

                $pr->update(['status' => 'converted_to_rfq']);

                $event = new PurchaseRequisitionApproved(
                    module: 'OrderSupplies',
                    payload: ['pr_id' => $pr->id, 'rfq_id' => $rfq->id, 'mode' => 'RFQ_REQUIRED'],
                    referenceNo: $pr->requisition_number,
                    userId: $userId,
                    branchId: 1
                );
                $this->eventBus->dispatch($event->eventData);

                return [
                    'pr' => $pr,
                    'target' => 'rfq',
                    'document' => $rfq,
                    'route' => route('procurement.rfqs.index'),
                    'message' => "Purchase Requisition {$pr->requisition_number} approved successfully. RFQ {$rfq->rfq_number} generated.",
                ];
            } else {
                $supplier = Supplier::where('status', 'active')->firstOrFail();
                $poNumber = $this->numGen->generate('PO', 'purchase_orders', 'po_number');

                $po = PurchaseOrder::create([
                    'po_number' => $poNumber,
                    'supplier_id' => $supplier->id,
                    'total_amount' => $totalAmount,
                    'tax_amount' => $totalAmount * 0.18,
                    'status' => 'submitted',
                    'shipment_status' => 'pending_dispatch',
                    'payment_terms' => $supplier->payment_terms ?? 'Net 30 Days',
                    'created_by' => $userId,
                ]);

                foreach ($pr->items as $item) {
                    $po->items()->create([
                        'product_id' => $item->product_id,
                        'quantity_ordered' => $item->quantity_requested,
                        'quantity_received' => 0,
                        'unit_cost' => (float) $item->estimated_unit_cost,
                        'total_cost' => $item->quantity_requested * (float) $item->estimated_unit_cost,
                    ]);
                }

                $pr->update(['status' => 'converted_to_po']);

                SendSupplierPoEmailJob::dispatch($po->id);
                GenerateProcurementPdfJob::dispatch('PurchaseOrder', $po->po_number);

                $event = new PurchaseRequisitionApproved(
                    module: 'OrderSupplies',
                    payload: ['pr_id' => $pr->id, 'po_id' => $po->id, 'mode' => 'DIRECT_PO'],
                    referenceNo: $pr->requisition_number,
                    userId: $userId,
                    branchId: 1
                );
                $this->eventBus->dispatch($event->eventData);

                return [
                    'pr' => $pr,
                    'target' => 'po',
                    'document' => $po,
                    'route' => route('procurement.purchase-orders.index'),
                    'message' => "Purchase Requisition {$pr->requisition_number} approved successfully. Draft Purchase Order {$po->po_number} created.",
                ];
            }
        });
    }

    /**
     * Reject Purchase Requisition (Enterprise Approval Engine)
     */
    public function rejectRequisition(int $prId, int $userId, string $reason, ?string $comments = null): PurchaseRequisition
    {
        return DB::transaction(function () use ($prId, $userId, $reason, $comments) {
            $pr = PurchaseRequisition::findOrFail($prId);

            if ($pr->status !== 'pending_approval') {
                throw new DomainException("Purchase Requisition {$pr->requisition_number} cannot be rejected because its current status is {$pr->status}.");
            }

            if (empty(trim($reason))) {
                throw new InvalidArgumentException("A mandatory rejection reason must be provided.");
            }

            $pr->update([
                'status' => 'rejected',
                'rejected_by' => $userId,
                'rejected_at' => now(),
                'rejection_reason' => $reason,
                'comments' => $comments,
            ]);

            return $pr;
        });
    }

    /**
     * Issue Purchase Order
     */
    public function createPurchaseOrder(int $supplierId, int $createdBy, array $items, float $discount = 0.0): PurchaseOrder
    {
        $supplier = Supplier::findOrFail($supplierId);
        $this->approvalEngine->validateSupplier($supplier);

        $poNumber = $this->numGen->generate('PO', 'purchase_orders', 'po_number');
        $totals = $this->mathEngine->calculateOrderTotals($items, $discount);

        $po = PurchaseOrder::create([
            'po_number' => $poNumber,
            'supplier_id' => $supplier->id,
            'total_amount' => $totals['grand_total'],
            'tax_amount' => $totals['tax_amount'],
            'status' => 'submitted',
            'shipment_status' => 'pending_dispatch',
            'payment_terms' => $supplier->payment_terms,
            'created_by' => $createdBy,
        ]);

        foreach ($items as $item) {
            $qty = (int) $item['quantity'];
            $unitCost = (float) $item['unit_cost'];
            $po->items()->create([
                'product_id' => $item['product_id'],
                'quantity_ordered' => $qty,
                'quantity_received' => 0,
                'unit_cost' => $unitCost,
                'total_cost' => $qty * $unitCost,
            ]);
        }

        SendSupplierPoEmailJob::dispatch($po->id);
        GenerateProcurementPdfJob::dispatch('PurchaseOrder', $po->po_number);
        RecalculateVendorPerformanceJob::dispatch($po->supplier_id);

        $event = new PurchaseOrderApproved(
            module: 'OrderSupplies',
            payload: ['po_id' => $po->id, 'total_amount' => $totals['grand_total']],
            referenceNo: $po->po_number,
            userId: $createdBy,
            branchId: 1
        );
        $this->eventBus->dispatch($event->eventData);

        return $po;
    }

    /**
     * Dispatch Supplier Inbound Shipment
     */
    public function dispatchShipment(int $poId, int $userId, array $details = []): PurchaseOrder
    {
        return DB::transaction(function () use ($poId, $userId, $details) {
            $po = PurchaseOrder::findOrFail($poId);

            if (in_array($po->status, ['completed', 'closed', 'cancelled'])) {
                throw new DomainException("Purchase Order {$po->po_number} is {$po->status} and cannot be dispatched.");
            }

            $leadTimeDays = (int) ($details['lead_time_days'] ?? 7);
            $expectedDelivery = Carbon::now()->addDays($leadTimeDays);

            $po->update([
                'shipment_status' => 'in_transit',
                'dispatch_date' => now(),
                'expected_delivery_date' => $expectedDelivery,
                'carrier_name' => $details['carrier_name'] ?? 'Express Logistics Hub',
                'tracking_reference' => $details['tracking_reference'] ?? ('TRK-' . strtoupper(uniqid())),
                'vehicle_number' => $details['vehicle_number'] ?? 'MH-04-EX-9988',
            ]);

            $event = new ShipmentDispatched(
                module: 'OrderSupplies',
                payload: [
                    'po_id' => $po->id,
                    'expected_delivery' => $expectedDelivery->toDateTimeString(),
                ],
                referenceNo: $po->po_number,
                userId: $userId,
                branchId: 1
            );
            $this->eventBus->dispatch($event->eventData);

            return $po;
        });
    }

    /**
     * Mark Inbound Shipment Arrived (Enters Pending Goods Receipt Queue)
     */
    public function markShipmentArrived(int $poId, int $userId): PurchaseOrder
    {
        return DB::transaction(function () use ($poId, $userId) {
            $po = PurchaseOrder::findOrFail($poId);

            if (in_array($po->shipment_status, ['arrived', 'completed'])) {
                return $po;
            }

            $po->update([
                'shipment_status' => 'arrived',
                'actual_arrival_date' => now(),
            ]);

            $event = new ShipmentArrived(
                module: 'OrderSupplies',
                payload: ['po_id' => $po->id, 'arrival_time' => now()->toDateTimeString()],
                referenceNo: $po->po_number,
                userId: $userId,
                branchId: 1
            );
            $this->eventBus->dispatch($event->eventData);

            return $po;
        });
    }

    /**
     * Log Goods Receipt Note & Trigger ERP Core Put-Away Automation (Partial & Full Receiving)
     */
    public function logGoodsReceipt(int $poId, int $receivedBy, ?int $warehouseId, ?string $challanNo, ?array $itemsReceived = null): GoodsReceiptNote
    {
        return DB::transaction(function () use ($poId, $receivedBy, $warehouseId, $challanNo, $itemsReceived) {
            $po = PurchaseOrder::with(['items.product', 'supplier'])->findOrFail($poId);

            if (in_array($po->status, ['completed', 'closed', 'cancelled', 'rejected'])) {
                throw new DomainException("Purchase Order {$po->po_number} is already {$po->status} and cannot accept further goods receipts.");
            }

            $grnNumber = $this->numGen->generate('GRN', 'goods_receipt_notes', 'grn_number');

            $grn = GoodsReceiptNote::create([
                'grn_number' => $grnNumber,
                'purchase_order_id' => $po->id,
                'supplier_id' => $po->supplier_id,
                'warehouse_id' => $warehouseId ?? 1,
                'delivery_challan_no' => $challanNo ?? ('DC-' . strtoupper(uniqid())),
                'status' => 'received',
                'received_by' => $receivedBy,
            ]);

            $allFullyReceived = true;

            foreach ($po->items as $item) {
                $remainingQty = max(0, $item->quantity_ordered - $item->quantity_received);
                $receivedQtyThisBatch = isset($itemsReceived[$item->id]) ? min($remainingQty, (int)$itemsReceived[$item->id]) : $remainingQty;

                if ($receivedQtyThisBatch <= 0) {
                    if ($item->quantity_received < $item->quantity_ordered) {
                        $allFullyReceived = false;
                    }
                    continue;
                }

                $newQtyReceivedTotal = $item->quantity_received + $receivedQtyThisBatch;
                $item->update(['quantity_received' => $newQtyReceivedTotal]);

                if ($newQtyReceivedTotal < $item->quantity_ordered) {
                    $allFullyReceived = false;
                }

                // Update Weighted Average Cost (WAC)
                $this->mathEngine->updateWeightedAverageCost($item->product, $receivedQtyThisBatch, (float) $item->unit_cost);

                // Dispatch GoodsReceiptCompleted to ERP Core Put-Away Automation & SSOT Sync
                $event = new GoodsReceiptCompleted(
                    productId: $item->product_id,
                    quantity: $receivedQtyThisBatch,
                    unitCost: (float) $item->unit_cost,
                    batchNumber: null,
                    storageCondition: 'Ambient Room Temperature',
                    qcStatus: 'Passed',
                    referenceNo: $grn->grn_number,
                    userId: $receivedBy
                );
                $this->eventBus->dispatch($event->eventData);
            }

            $newPoStatus = $allFullyReceived ? 'completed' : 'partial_received';
            $newShipmentStatus = $allFullyReceived ? 'completed' : 'arrived';

            $po->update([
                'status' => $newPoStatus,
                'shipment_status' => $newShipmentStatus,
                'received_at' => now(),
                'received_by' => $receivedBy,
            ]);

            GenerateProcurementPdfJob::dispatch('GoodsReceiptNote', $grn->grn_number);

            return $grn;
        });
    }

    /**
     * Reconcile Supplier Invoice (3-Way Matching)
     */
    public function matchInvoice(int $supplierId, ?int $poId, float $invoiceAmount, int $verifiedBy): SupplierInvoice
    {
        $invNumber = $this->numGen->generate('INV', 'supplier_invoices', 'invoice_number');
        $po = $poId ? PurchaseOrder::with('items')->find($poId) : null;
        $grn = $poId ? GoodsReceiptNote::where('purchase_order_id', $poId)->first() : null;

        $invoice = SupplierInvoice::create([
            'invoice_number' => $invNumber,
            'supplier_id' => $supplierId,
            'purchase_order_id' => $poId,
            'total_amount' => $invoiceAmount,
            'match_status' => 'pending_matching',
            'verified_by' => $verifiedBy,
        ]);

        if ($po) {
            $matchResult = $this->matchEngine->match($po, $grn, $invoice);
            $invoice->update(['match_status' => $matchResult['match_status']]);

            if ($matchResult['is_matched']) {
                $event = new InvoiceMatched(
                    module: 'OrderSupplies',
                    payload: ['invoice_id' => $invoice->id, 'amount' => $invoiceAmount],
                    referenceNo: $invoice->invoice_number,
                    userId: $verifiedBy,
                    branchId: 1
                );
                $this->eventBus->dispatch($event->eventData);
            }
        }

        return $invoice;
    }
}

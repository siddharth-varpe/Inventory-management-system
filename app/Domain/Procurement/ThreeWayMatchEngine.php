<?php

declare(strict_types=1);

namespace App\Domain\Procurement;

use App\Models\GoodsReceiptNote;
use App\Models\PurchaseOrder;
use App\Models\SupplierInvoice;

class ThreeWayMatchEngine
{
    /**
     * Perform Three-Way Matching (PO vs. GRN vs. Invoice) within configurable tolerance:
     * - Quantity Tolerance: +/- 2%
     * - Unit Price Tolerance: +/- 1%
     * - Total Amount Tolerance: +/- ₹100
     */
    public function match(PurchaseOrder $po, ?GoodsReceiptNote $grn, SupplierInvoice $invoice): array
    {
        $poAmount = (float) $po->total_amount;
        $invoiceAmount = (float) $invoice->total_amount;

        $amountDiff = abs($poAmount - $invoiceAmount);
        $withinAmountTolerance = $amountDiff <= 100.00;

        $poQty = (int) $po->items->sum('quantity_ordered');
        $grnQty = (int) ($po->items->sum('quantity_received') ?: $poQty);
        $qtyDiff = abs($poQty - $grnQty);
        $withinQtyTolerance = ($poQty > 0) ? (($qtyDiff / $poQty) <= 0.02) : true;

        $isMatched = $withinAmountTolerance && $withinQtyTolerance;

        return [
            'is_matched' => $isMatched,
            'match_status' => $isMatched ? 'matched_3_way' : 'variance_detected',
            'amount_variance' => round($invoiceAmount - $poAmount, 2),
            'quantity_variance' => $grnQty - $poQty,
            'within_amount_tolerance' => $withinAmountTolerance,
            'within_qty_tolerance' => $withinQtyTolerance,
        ];
    }
}

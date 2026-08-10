<?php

declare(strict_types=1);

namespace App\Domain\Sales;

use App\Models\Quotation;
use App\Models\Customer;
use App\Models\Product;
use App\Domain\Communication\CommunicationEngineService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

class QuotationService
{
    public function __construct(
        protected CustomerPricingService $pricingService,
        protected SalesGstCalculator $gstCalculator,
        protected CommunicationEngineService $cceService
    ) {}

    /**
     * Create a new Quotation from Cart Items & Customer metadata.
     * Automatically integrates with Customer Communication Engine (CCE) Phase 2.
     */
    public function createQuotation(Customer $customer, int $userId, array $cartItems, array $options = []): Quotation
    {
        return DB::transaction(function () use ($customer, $userId, $cartItems, $options) {
            $nextId = Quotation::max('id') + 1;
            $quotationNumber = 'QTN-' . date('Y') . '-' . str_pad((string)$nextId, 5, '0', STR_PAD_LEFT);

            $validityDays = (int)($options['validity_days'] ?? 30);
            $validityDate = Carbon::now()->addDays($validityDays)->toDateString();

            $subtotal = 0.00;
            $totalTaxable = 0.00;
            $totalCgst = 0.00;
            $totalSgst = 0.00;
            $totalIgst = 0.00;
            $totalTax = 0.00;
            $grandTotal = 0.00;

            $itemsToCreate = [];
            $maxDiscountApplied = 0.0;

            foreach ($cartItems as $item) {
                $product = Product::where('id', $item['product_id'])->lockForUpdate()->firstOrFail();
                if ($product->status !== 'active') {
                    throw new InvalidArgumentException("Product '{$product->name}' is inactive and cannot be added to a quotation.");
                }

                $qty = (int)($item['quantity'] ?? 0);
                if ($qty <= 0) {
                    throw new InvalidArgumentException("Product quantity for '{$product->name}' must be greater than zero.");
                }

                $physical = (int)$product->physical_stock;
                $reserved = (int)$product->reserved_stock;
                $available = max(0, $physical - $reserved);

                if ($available <= 0) {
                    throw new InvalidArgumentException("Product '{$product->name}' (SKU: {$product->sku}) is currently OUT OF STOCK (Available: 0).");
                }

                if ($qty > $available) {
                    $shortage = $qty - $available;
                    throw new InvalidArgumentException("Insufficient Stock for '{$product->name}' (SKU: {$product->sku}). Requested: {$qty}, Available: {$available}, Shortage: {$shortage}.");
                }

                $unitCost = (float)$product->cost_price;
                $unitPrice = isset($item['unit_price']) ? (float)$item['unit_price'] : $this->pricingService->getCustomerPrice($product, $customer);

                $discountType = $item['discount_type'] ?? 'percentage';
                $discountVal = (float)($item['discount_amount'] ?? 0);

                if ($discountType === 'percentage') {
                    $lineDiscount = round(($unitPrice * $qty) * ($discountVal / 100.0), 2);
                    $maxDiscountApplied = max($maxDiscountApplied, $discountVal);
                } else {
                    $lineDiscount = round($discountVal, 2);
                }

                $lineSubtotal = round($unitPrice * $qty, 2);
                $lineTaxable = max(0.00, $lineSubtotal - $lineDiscount);

                // Get GST tax rate
                $gstRate = (float)($product->tax->rate ?? 18.00);
                $taxSplit = $this->gstCalculator->calculateTax($lineTaxable, $gstRate, $customer);

                $lineGst = $taxSplit['total_tax'];
                $lineTotal = $lineTaxable + $lineGst;

                $subtotal += $lineSubtotal;
                $totalTaxable += $lineTaxable;
                $totalCgst += $taxSplit['cgst'];
                $totalSgst += $taxSplit['sgst'];
                $totalIgst += $taxSplit['igst'];
                $totalTax += $lineGst;
                $grandTotal += $lineTotal;

                $itemsToCreate[] = [
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_cost' => $unitCost,
                    'unit_price' => $unitPrice,
                    'discount_type' => $discountType,
                    'discount_amount' => $discountVal,
                    'taxable_value' => $lineTaxable,
                    'gst_rate' => $gstRate,
                    'gst_amount' => $lineGst,
                    'line_total' => $lineTotal,
                    'remarks' => $item['remarks'] ?? null,
                ];
            }

            // Order level discount
            $orderDiscountVal = (float)($options['order_discount_amount'] ?? 0);
            $orderDiscountType = $options['order_discount_type'] ?? 'fixed';

            if ($orderDiscountVal > 0) {
                if ($orderDiscountType === 'percentage') {
                    $orderDiscountAmt = round($totalTaxable * ($orderDiscountVal / 100.0), 2);
                } else {
                    $orderDiscountAmt = $orderDiscountVal;
                }
                $totalTaxable = max(0.00, $totalTaxable - $orderDiscountAmt);
                $grandTotal = $totalTaxable + $totalTax;
            } else {
                $orderDiscountAmt = 0.00;
            }

            // High Value / High Discount Manager Approval Rule
            $requiresApproval = ($grandTotal >= 500000.00 || $maxDiscountApplied >= 20.0);
            $initialStatus = $requiresApproval ? 'pending_approval' : 'approved';

            $quotation = Quotation::create([
                'quotation_number' => $quotationNumber,
                'customer_id' => $customer->id,
                'salesperson_id' => $userId,
                'validity_date' => $validityDate,
                'status' => $initialStatus,
                'subtotal' => $subtotal,
                'order_discount_type' => $orderDiscountType,
                'order_discount_amount' => $orderDiscountAmt,
                'taxable_amount' => $totalTaxable,
                'cgst_amount' => $totalCgst,
                'sgst_amount' => $totalSgst,
                'igst_amount' => $totalIgst,
                'tax_amount' => $totalTax,
                'grand_total' => $grandTotal,
                'delivery_terms' => $options['delivery_terms'] ?? 'Standard Delivery (2-3 business days)',
                'payment_terms' => $options['payment_terms'] ?? $customer->payment_term,
                'remarks' => $options['remarks'] ?? null,
                'internal_notes' => $options['internal_notes'] ?? null,
                'created_by' => $userId,
                'approved_by' => $requiresApproval ? null : $userId,
                'approved_at' => $requiresApproval ? null : now(),
            ]);

            foreach ($itemsToCreate as $itemData) {
                $quotation->items()->create($itemData);
            }

            // ===================================================================
            // CCE INTEGRATION (PHASE 2): AUTOMATIC COMMUNICATION PREPARATION
            // ===================================================================
            $pdfRef = "storage/app/quotations/{$quotation->quotation_number}_v1.0.pdf";

            $cceRecord = $this->cceService->createRecord([
                'customer_id' => $customer->id,
                'related_document_type' => 'Quotation',
                'related_document_id' => $quotation->id,
                'enterprise_order_id' => $quotation->quotation_number,
                'document_version' => '1.0',
                'attachment_reference' => $pdfRef,
                'subject' => "Commercial Quotation Proposal #{$quotation->quotation_number}",
                'message_preview' => "Dear {$customer->company_name}, please find attached commercial quotation proposal #{$quotation->quotation_number} for total amount ₹" . number_format((float)$quotation->grand_total, 2) . ".",
                'metadata' => [
                    'quotation_number' => $quotation->quotation_number,
                    'grand_total' => $quotation->grand_total,
                    'items_count' => count($itemsToCreate),
                ],
            ], $userId, 'Sales');

            // Transition CCE record to Prepared state
            $this->cceService->prepareCommunication($cceRecord, null, null, $userId);

            // Log CCE PDF Preparation & Audit
            $cceRecord->logTimeline("PDF Prepared", "prepared", "prepared", "Generated enterprise quotation PDF reference: {$pdfRef}", $userId);
            $cceRecord->logAudit("PDF_PREPARED", $userId, "Sales", ['pdf_reference' => $pdfRef]);

            return $quotation;
        });
    }

    /**
     * Update an existing Quotation with new cart items & metadata.
     * Enforces converted protection & real-time stock validation.
     */
    public function updateQuotation(Quotation $quotation, Customer $customer, int $userId, array $cartItems, array $options = []): Quotation
    {
        if ($quotation->status === 'converted' || $quotation->sales_order_id !== null) {
            throw new InvalidArgumentException("Quotation #{$quotation->quotation_number} has already been converted to a Sales Order and cannot be edited.");
        }

        return DB::transaction(function () use ($quotation, $customer, $userId, $cartItems, $options) {
            $validityDays = (int)($options['validity_days'] ?? 30);
            $validityDate = Carbon::now()->addDays($validityDays)->toDateString();

            $subtotal = 0.00;
            $totalTaxable = 0.00;
            $totalCgst = 0.00;
            $totalSgst = 0.00;
            $totalIgst = 0.00;
            $totalTax = 0.00;
            $grandTotal = 0.00;

            $itemsToCreate = [];
            $maxDiscountApplied = 0.0;

            foreach ($cartItems as $item) {
                $product = Product::where('id', $item['product_id'])->lockForUpdate()->firstOrFail();
                if ($product->status !== 'active') {
                    throw new InvalidArgumentException("Product '{$product->name}' is inactive and cannot be added to a quotation.");
                }

                $qty = (int)($item['quantity'] ?? 0);
                if ($qty <= 0) {
                    throw new InvalidArgumentException("Product quantity for '{$product->name}' must be greater than zero.");
                }

                $physical = (int)$product->physical_stock;
                $reserved = (int)$product->reserved_stock;
                $available = max(0, $physical - $reserved);

                if ($available <= 0) {
                    throw new InvalidArgumentException("Product '{$product->name}' (SKU: {$product->sku}) is currently OUT OF STOCK (Available: 0).");
                }

                if ($qty > $available) {
                    $shortage = $qty - $available;
                    throw new InvalidArgumentException("Insufficient Stock for '{$product->name}' (SKU: {$product->sku}). Requested: {$qty}, Available: {$available}, Shortage: {$shortage}.");
                }

                $unitCost = (float)$product->cost_price;
                $unitPrice = isset($item['unit_price']) ? (float)$item['unit_price'] : $this->pricingService->getCustomerPrice($product, $customer);

                $discountType = $item['discount_type'] ?? 'percentage';
                $discountVal = (float)($item['discount_amount'] ?? 0);

                if ($discountType === 'percentage') {
                    $lineDiscount = round(($unitPrice * $qty) * ($discountVal / 100.0), 2);
                    $maxDiscountApplied = max($maxDiscountApplied, $discountVal);
                } else {
                    $lineDiscount = round($discountVal, 2);
                }

                $lineSubtotal = round($unitPrice * $qty, 2);
                $lineTaxable = max(0.00, $lineSubtotal - $lineDiscount);

                $gstRate = (float)($product->tax->rate ?? 18.00);
                $taxSplit = $this->gstCalculator->calculateTax($lineTaxable, $gstRate, $customer);

                $lineGst = $taxSplit['total_tax'];
                $lineTotal = $lineTaxable + $lineGst;

                $subtotal += $lineSubtotal;
                $totalTaxable += $lineTaxable;
                $totalCgst += $taxSplit['cgst'];
                $totalSgst += $taxSplit['sgst'];
                $totalIgst += $taxSplit['igst'];
                $totalTax += $lineGst;
                $grandTotal += $lineTotal;

                $itemsToCreate[] = [
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_cost' => $unitCost,
                    'unit_price' => $unitPrice,
                    'discount_type' => $discountType,
                    'discount_amount' => $discountVal,
                    'taxable_value' => $lineTaxable,
                    'gst_rate' => $gstRate,
                    'gst_amount' => $lineGst,
                    'line_total' => $lineTotal,
                    'remarks' => $item['remarks'] ?? null,
                ];
            }

            $orderDiscountVal = (float)($options['order_discount_amount'] ?? 0);
            $orderDiscountType = $options['order_discount_type'] ?? 'fixed';

            if ($orderDiscountVal > 0) {
                if ($orderDiscountType === 'percentage') {
                    $orderDiscountAmt = round($totalTaxable * ($orderDiscountVal / 100.0), 2);
                } else {
                    $orderDiscountAmt = $orderDiscountVal;
                }
                $totalTaxable = max(0.00, $totalTaxable - $orderDiscountAmt);
                $grandTotal = $totalTaxable + $totalTax;
            } else {
                $orderDiscountAmt = 0.00;
            }

            $requiresApproval = ($grandTotal >= 500000.00 || $maxDiscountApplied >= 20.0);
            $status = $requiresApproval ? 'pending_approval' : ($quotation->status === 'rejected' ? 'draft' : $quotation->status);

            $quotation->update([
                'customer_id' => $customer->id,
                'validity_date' => $validityDate,
                'status' => $status,
                'subtotal' => $subtotal,
                'order_discount_type' => $orderDiscountType,
                'order_discount_amount' => $orderDiscountAmt,
                'taxable_amount' => $totalTaxable,
                'cgst_amount' => $totalCgst,
                'sgst_amount' => $totalSgst,
                'igst_amount' => $totalIgst,
                'tax_amount' => $totalTax,
                'grand_total' => $grandTotal,
                'delivery_terms' => $options['delivery_terms'] ?? $quotation->delivery_terms,
                'payment_terms' => $options['payment_terms'] ?? $customer->payment_term,
                'remarks' => $options['remarks'] ?? $quotation->remarks,
            ]);

            $quotation->items()->delete();
            foreach ($itemsToCreate as $itemData) {
                $quotation->items()->create($itemData);
            }

            return $quotation;
        });
    }

    /**
     * Delete an existing Quotation.
     * Enforces converted protection & database safety.
     */
    public function deleteQuotation(Quotation $quotation): void
    {
        if ($quotation->status === 'converted' || $quotation->sales_order_id !== null) {
            throw new InvalidArgumentException("Quotation #{$quotation->quotation_number} has already been converted to a Sales Order and cannot be deleted.");
        }

        DB::transaction(function () use ($quotation) {
            $quotation->items()->delete();
            $quotation->delete();
        });
    }

    /**
     * Duplicate an existing Quotation into a new Draft Quotation.
     * Integrates with CCE Phase 2 (Document Revision 2.0).
     */
    public function duplicateQuotation(Quotation $quotation, int $userId): Quotation
    {
        return DB::transaction(function () use ($quotation, $userId) {
            $nextId = Quotation::max('id') + 1;
            $newNumber = 'QTN-' . date('Y') . '-' . str_pad((string)$nextId, 5, '0', STR_PAD_LEFT);

            $newQuotation = Quotation::create([
                'quotation_number' => $newNumber,
                'customer_id' => $quotation->customer_id,
                'salesperson_id' => $userId,
                'validity_date' => Carbon::now()->addDays(30)->toDateString(),
                'status' => 'draft',
                'subtotal' => $quotation->subtotal,
                'order_discount_type' => $quotation->order_discount_type,
                'order_discount_amount' => $quotation->order_discount_amount,
                'taxable_amount' => $quotation->taxable_amount,
                'cgst_amount' => $quotation->cgst_amount,
                'sgst_amount' => $quotation->sgst_amount,
                'igst_amount' => $quotation->igst_amount,
                'tax_amount' => $quotation->tax_amount,
                'grand_total' => $quotation->grand_total,
                'delivery_terms' => $quotation->delivery_terms,
                'payment_terms' => $quotation->payment_terms,
                'remarks' => $quotation->remarks,
                'internal_notes' => "Duplicated from Quotation #" . $quotation->quotation_number,
                'created_by' => $userId,
            ]);

            foreach ($quotation->items as $item) {
                $newQuotation->items()->create([
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'unit_cost' => $item->unit_cost,
                    'unit_price' => $item->unit_price,
                    'discount_type' => $item->discount_type,
                    'discount_amount' => $item->discount_amount,
                    'taxable_value' => $item->taxable_value,
                    'gst_rate' => $item->gst_rate,
                    'gst_amount' => $item->gst_amount,
                    'line_total' => $item->line_total,
                    'remarks' => $item->remarks,
                ]);
            }

            // ===================================================================
            // CCE INTEGRATION (PHASE 2): DUPLICATE / REVISION COMMUNICATION RECORD
            // ===================================================================
            $pdfRef = "storage/app/quotations/{$newQuotation->quotation_number}_v2.0.pdf";

            $cceRecord = $this->cceService->createRecord([
                'customer_id' => $newQuotation->customer_id,
                'related_document_type' => 'Quotation',
                'related_document_id' => $newQuotation->id,
                'enterprise_order_id' => $newQuotation->quotation_number,
                'document_version' => '2.0',
                'attachment_reference' => $pdfRef,
                'subject' => "Commercial Quotation Proposal #{$newQuotation->quotation_number} (Revision 2.0)",
                'message_preview' => "Dear {$newQuotation->customer->company_name}, please review revised quotation #{$newQuotation->quotation_number} for total amount ₹" . number_format((float)$newQuotation->grand_total, 2) . ".",
                'metadata' => [
                    'quotation_number' => $newQuotation->quotation_number,
                    'grand_total' => $newQuotation->grand_total,
                    'duplicated_from' => $quotation->quotation_number,
                ],
            ], $userId, 'Sales');

            $this->cceService->prepareCommunication($cceRecord, null, null, $userId);

            $cceRecord->logTimeline("PDF Prepared (Revision 2.0)", "prepared", "prepared", "Generated revised quotation PDF reference: {$pdfRef}", $userId);
            $cceRecord->logAudit("PDF_PREPARED_REVISION", $userId, "Sales", ['pdf_reference' => $pdfRef, 'revision' => '2.0']);

            return $newQuotation;
        });
    }

    /**
     * Validate Quotation for Conversion Eligibility (including live Stock Availability).
     */
    public function validateConversionEligibility(Quotation $quotation, bool $lockForUpdate = false): void
    {
        if ($quotation->status === 'converted' || $quotation->sales_order_id !== null) {
            $linkedOrder = $quotation->salesOrder ? " (#{$quotation->salesOrder->order_number})" : '';
            throw new InvalidArgumentException("Quotation #{$quotation->quotation_number} has already been converted to a Sales Order{$linkedOrder}.");
        }

        if (!in_array($quotation->status, ['approved', 'customer_accepted'])) {
            throw new InvalidArgumentException("Only approved or customer accepted quotations can be converted to Sales Orders. Current status: " . strtoupper($quotation->status));
        }

        if ($quotation->validity_date && $quotation->validity_date->isPast()) {
            throw new InvalidArgumentException("Quotation #{$quotation->quotation_number} expired on " . $quotation->validity_date->format('d M Y') . " and cannot be converted.");
        }

        if ($quotation->customer && $quotation->customer->status !== 'active') {
            throw new InvalidArgumentException("Customer account '{$quotation->customer->company_name}' is inactive or blocked.");
        }

        $shortages = [];
        foreach ($quotation->items as $item) {
            if ($item->quantity <= 0) {
                throw new InvalidArgumentException("Quotation item quantity must be greater than zero.");
            }

            $productQuery = \App\Models\Product::where('id', $item->product_id);
            if ($lockForUpdate) {
                $productQuery->lockForUpdate();
            }
            $product = $productQuery->first();

            if (!$product) {
                throw new InvalidArgumentException("Line item product reference (ID: {$item->product_id}) is missing or no longer exists in inventory master.");
            }

            if ($product->status !== 'active') {
                throw new InvalidArgumentException("Line item product '{$product->name}' (SKU: {$product->sku}) is inactive in inventory master.");
            }

            $physical = (int)$product->physical_stock;
            $reserved = (int)$product->reserved_stock;
            $available = max(0, $physical - $reserved);

            if ($available < $item->quantity) {
                $shortageAmount = $item->quantity - $available;
                $shortages[] = "'{$product->name}' (SKU: {$product->sku}): Available={$available}, Required={$item->quantity}, Shortage={$shortageAmount}";
            }
        }

        if (!empty($shortages)) {
            throw new InvalidArgumentException("Unable to Convert Quotation #{$quotation->quotation_number}. Insufficient available stock for item(s): " . implode('; ', $shortages));
        }
    }

    public function approveQuotation(Quotation $quotation, int $approverId): Quotation
    {
        $quotation->update([
            'status' => 'approved',
            'approved_by' => $approverId,
            'approved_at' => now(),
        ]);

        return $quotation;
    }

    public function rejectQuotation(Quotation $quotation, int $approverId, string $reason): Quotation
    {
        $quotation->update([
            'status' => 'rejected',
            'internal_notes' => ($quotation->internal_notes ? $quotation->internal_notes . "\n" : '') . "Rejection Reason: " . $reason,
        ]);

        return $quotation;
    }
}

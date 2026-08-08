<?php

declare(strict_types=1);

namespace App\Domain\Procurement;

use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ProcurementMathEngine
{
    /**
     * Calculate Subtotal, Tax (18% GST), Discount, Landed Cost, and Grand Total.
     */
    public function calculateOrderTotals(array $items, float $discount = 0.0, float $landedCharges = 0.0): array
    {
        $subtotal = 0.0;

        foreach ($items as $item) {
            $qty = (int) ($item['quantity'] ?? 0);
            $cost = (float) ($item['unit_cost'] ?? 0.0);
            $subtotal += ($qty * $cost);
        }

        $taxableAmount = max(0.0, $subtotal - $discount);
        $taxAmount = $taxableAmount * 0.18; // 18% GST standard
        $grandTotal = $taxableAmount + $taxAmount + $landedCharges;

        return [
            'subtotal' => round($subtotal, 2),
            'discount' => round($discount, 2),
            'taxable_amount' => round($taxableAmount, 2),
            'tax_amount' => round($taxAmount, 2),
            'landed_charges' => round($landedCharges, 2),
            'grand_total' => round($grandTotal, 2),
        ];
    }

    /**
     * Calculate Weighted Average Cost (WAC) and update Product cost price.
     * WAC = (Current Stock Value + New Receipt Value) / (Current Qty + New Qty)
     */
    public function updateWeightedAverageCost(Product $product, int $receivedQty, float $unitCost): float
    {
        return DB::transaction(function () use ($product, $receivedQty, $unitCost) {
            $currentStock = (int) Inventory::where('product_id', $product->id)->sum('quantity');
            $currentCost = (float) $product->cost_price;

            $currentValuation = $currentStock * $currentCost;
            $newValuation = $receivedQty * $unitCost;

            $totalQty = $currentStock + $receivedQty;

            if ($totalQty > 0) {
                $newWac = ($currentValuation + $newValuation) / $totalQty;
            } else {
                $newWac = $unitCost;
            }

            $product->update([
                'cost_price' => round($newWac, 2),
            ]);

            return round($newWac, 2);
        });
    }
}

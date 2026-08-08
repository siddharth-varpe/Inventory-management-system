<?php

declare(strict_types=1);

namespace App\Domain\Procurement;

class LandedCostEngine
{
    /**
     * Allocate landed expenses (freight, customs, duty, insurance, handling)
     * pro-rata by value across received items.
     */
    public function allocate(array $items, float $freight = 0.0, float $duty = 0.0, float $insurance = 0.0, float $handling = 0.0): array
    {
        $totalLandedExpenses = $freight + $duty + $insurance + $handling;
        $totalPurchaseValue = 0.0;

        foreach ($items as $item) {
            $totalPurchaseValue += ((int) $item['quantity']) * ((float) $item['unit_cost']);
        }

        $allocatedItems = [];

        foreach ($items as $item) {
            $qty = (int) $item['quantity'];
            $baseCost = (float) $item['unit_cost'];
            $itemValue = $qty * $baseCost;

            if ($totalPurchaseValue > 0) {
                $allocatedExpense = ($itemValue / $totalPurchaseValue) * $totalLandedExpenses;
            } else {
                $allocatedExpense = 0.0;
            }

            $perUnitLandedExpense = ($qty > 0) ? ($allocatedExpense / $qty) : 0.0;
            $finalLandedUnitCost = $baseCost + $perUnitLandedExpense;

            $allocatedItems[] = array_merge($item, [
                'allocated_expense' => round($allocatedExpense, 2),
                'per_unit_landed_expense' => round($perUnitLandedExpense, 2),
                'landed_unit_cost' => round($finalLandedUnitCost, 2),
                'total_landed_cost' => round($qty * $finalLandedUnitCost, 2),
            ]);
        }

        return [
            'total_landed_expenses' => round($totalLandedExpenses, 2),
            'total_purchase_value' => round($totalPurchaseValue, 2),
            'allocated_items' => $allocatedItems,
        ];
    }
}

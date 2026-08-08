<?php

declare(strict_types=1);

namespace App\Services\Report;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\StockReceipt;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ReportService
{
    /**
     * Generate Inventory Valuation Report dataset.
     *
     * @return array<string, mixed>
     */
    public function getInventoryValuation(): array
    {
        $products = Product::with(['category', 'brand', 'unit'])->get();

        $totalValuationCost = $products->sum(fn ($p) => $p->physical_stock * $p->cost_price);
        $totalValuationSelling = $products->sum(fn ($p) => $p->physical_stock * $p->selling_price);
        $potentialProfit = $totalValuationSelling - $totalValuationCost;

        return [
            'products' => $products,
            'total_cost' => $totalValuationCost,
            'total_selling' => $totalValuationSelling,
            'potential_profit' => $potentialProfit,
        ];
    }

    /**
     * Generate Stock Ledger dataset.
     *
     * @return Collection
     */
    public function getStockLedger(): Collection
    {
        $receipts = StockReceipt::with(['product', 'creator'])
            ->get()
            ->map(function ($r) {
                return [
                    'date' => $r->created_at,
                    'reference_no' => $r->reference_no,
                    'product_name' => $r->product->name ?? 'N/A',
                    'sku' => $r->product->sku ?? 'N/A',
                    'type' => 'Stock Receive',
                    'quantity' => '+' . $r->quantity,
                    'unit_cost' => $r->unit_cost,
                    'total_amount' => $r->total_cost,
                    'user' => $r->creator->name ?? 'System',
                ];
            });

        $adjustments = StockAdjustment::with(['product', 'creator'])
            ->get()
            ->map(function ($a) {
                return [
                    'date' => $a->created_at,
                    'reference_no' => $a->reference_no,
                    'product_name' => $a->product->name ?? 'N/A',
                    'sku' => $a->product->sku ?? 'N/A',
                    'type' => 'Adjustment (' . ucfirst($a->type) . ')',
                    'quantity' => ($a->quantity > 0 ? '+' : '') . $a->quantity,
                    'unit_cost' => $a->unit_cost,
                    'total_amount' => $a->total_amount,
                    'user' => $a->creator->name ?? 'System',
                ];
            });

        return $receipts->concat($adjustments)->sortByDesc('date')->values();
    }

    /**
     * Generate Low Stock Report.
     *
     * @return Collection
     */
    public function getLowStock(): Collection
    {
        return Product::whereColumn('physical_stock', '<=', 'reorder_level')
            ->with(['category', 'brand', 'unit'])
            ->orderBy('physical_stock')
            ->get();
    }

    /**
     * Generate Dead Stock Report.
     *
     * @return Collection
     */
    public function getDeadStock(): Collection
    {
        return Product::where('physical_stock', '>', 0)
            ->whereDoesntHave('receipts', function ($q) {
                $q->where('created_at', '>=', Carbon::now()->subDays(60));
            })
            ->whereDoesntHave('adjustments', function ($q) {
                $q->where('created_at', '>=', Carbon::now()->subDays(60));
            })
            ->with(['category', 'brand'])
            ->get();
    }

    /**
     * Fast Moving Products (highest transacted volumes).
     *
     * @return Collection
     */
    public function getFastMoving(): Collection
    {
        return Product::withSum('receipts as total_received', 'quantity')
            ->withSum('adjustments as total_adjusted', 'quantity')
            ->orderByDesc('total_received')
            ->take(15)
            ->get();
    }

    /**
     * Slow Moving Products (lowest transacted volumes).
     *
     * @return Collection
     */
    public function getSlowMoving(): Collection
    {
        return Product::where('physical_stock', '>', 0)
            ->withSum('receipts as total_received', 'quantity')
            ->orderBy('total_received')
            ->take(15)
            ->get();
    }

    /**
     * Adjustment Loss Report.
     *
     * @return Collection
     */
    public function getAdjustmentReport(): Collection
    {
        return StockAdjustment::with(['product', 'creator', 'approver'])
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Expiry Schedule Report.
     *
     * @return Collection
     */
    public function getExpiryReport(): Collection
    {
        return Inventory::with('product')
            ->whereNotNull('expiry_date')
            ->orderBy('expiry_date')
            ->get();
    }
}

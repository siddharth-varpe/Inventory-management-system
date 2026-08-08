<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\StockReceipt;
use Carbon\Carbon;
use Illuminate\View\View;

class StockDashboardController extends Controller
{
    /**
     * Display Manage Stock Station Dashboard.
     */
    public function __invoke(): View
    {
        $metrics = [
            'total_products' => Product::count(),
            'active_products' => Product::where('status', 'active')->count(),
            'low_stock' => Product::whereColumn('physical_stock', '<=', 'reorder_level')->where('physical_stock', '>', 0)->count(),
            'out_of_stock' => Product::where('physical_stock', '<=', 0)->count(),
            'expiring_soon' => Inventory::whereNotNull('expiry_date')->whereBetween('expiry_date', [Carbon::today(), Carbon::today()->addDays(30)])->count(),
            'inventory_value' => (float) Product::all()->sum(fn ($p) => $p->physical_stock * $p->cost_price),
        ];

        $recentReceipts = StockReceipt::with('product')->orderByDesc('id')->take(6)->get();
        $recentAdjustments = StockAdjustment::with(['product', 'creator'])->orderByDesc('id')->take(6)->get();
        $lowStockProducts = Product::whereColumn('physical_stock', '<=', 'reorder_level')->take(5)->get();
        $categoryBreakdown = Category::withCount('products')->get();

        return view('stock.dashboard', compact('metrics', 'recentReceipts', 'recentAdjustments', 'lowStockProducts', 'categoryBreakdown'));
    }
}

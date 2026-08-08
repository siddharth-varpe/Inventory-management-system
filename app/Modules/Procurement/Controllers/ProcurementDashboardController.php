<?php

declare(strict_types=1);

namespace App\Modules\Procurement\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequisition;
use App\Models\Supplier;
use Illuminate\View\View;

class ProcurementDashboardController extends Controller
{
    public function index(): View
    {
        $kpis = [
            'active_suppliers' => Supplier::where('status', 'active')->count(),
            'pending_requisitions' => PurchaseRequisition::where('status', 'pending_approval')->count(),
            'open_purchase_orders' => PurchaseOrder::whereIn('status', ['submitted', 'approved', 'partial_received'])->count(),
            'completed_orders' => PurchaseOrder::where('status', 'completed')->count(),
        ];

        // Live Low Stock Procurement Recommendation Engine (SSOT Product Master)
        $purchaseRecommendations = Product::where('status', 'active')
            ->whereColumn('available_stock', '<=', 'reorder_level')
            ->with(['category', 'brand', 'unit'])
            ->orderBy('available_stock')
            ->get();

        $recentRequisitions = PurchaseRequisition::latest()->take(5)->get();
        $recentOrders = PurchaseOrder::with('supplier')->latest()->take(5)->get();

        return view('procurement.dashboard', compact('kpis', 'purchaseRecommendations', 'recentRequisitions', 'recentOrders'));
    }
}

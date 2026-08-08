<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\DispatchTask;
use App\Models\PickingTask;
use App\Models\StorageRequest;
use App\Models\Warehouse;
use App\Models\WarehouseException;
use App\Models\WarehouseTransfer;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrganizeDashboardController extends Controller
{
    public function index(): View
    {
        $kpis = [
            'total_warehouses' => Warehouse::count(),
            'active_warehouses' => Warehouse::where('status', 'active')->count(),
            'pending_putaway' => StorageRequest::whereIn('status', ['pending', 'assigned'])->count(),
            'pending_picks' => PickingTask::whereIn('status', ['pending', 'assigned', 'picking'])->count(),
            'completed_picks' => PickingTask::where('status', 'completed')->count(),
            'today_transfers' => WarehouseTransfer::whereDate('created_at', today())->count(),
            'today_exceptions' => WarehouseException::whereDate('created_at', today())->count(),
        ];

        $warehouses = Warehouse::with('zones.aisles.racks.bins')->take(5)->get();
        $recentPutaways = StorageRequest::with('product')->latest()->take(5)->get();
        $recentPicks = PickingTask::with('items.product')->latest()->take(5)->get();
        $recentExceptions = WarehouseException::with('product')->latest()->take(5)->get();

        return view('organize.dashboard', compact('kpis', 'warehouses', 'recentPutaways', 'recentPicks', 'recentExceptions'));
    }
}

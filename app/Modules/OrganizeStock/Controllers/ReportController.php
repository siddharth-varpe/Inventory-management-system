<?php

declare(strict_types=1);

namespace App\Modules\OrganizeStock\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DispatchTask;
use App\Models\PickingTask;
use App\Models\StorageRequest;
use App\Models\Warehouse;
use App\Models\WarehouseBin;
use App\Models\WarehouseException;
use App\Models\WarehouseTransfer;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $warehouses = Warehouse::with('zones.aisles.racks.bins')->get();

        $metrics = [
            'total_warehouses' => Warehouse::count(),
            'total_capacity' => Warehouse::sum('total_capacity'),
            'occupied_capacity' => Warehouse::sum('occupied_capacity'),
            'total_bins' => WarehouseBin::count(),
            'occupied_bins' => WarehouseBin::where('current_occupied_qty', '>', 0)->count(),
            'total_putaways' => StorageRequest::count(),
            'completed_putaways' => StorageRequest::where('status', 'completed')->count(),
            'total_picks' => PickingTask::count(),
            'completed_picks' => PickingTask::where('status', 'completed')->count(),
            'total_transfers' => WarehouseTransfer::count(),
            'total_exceptions' => WarehouseException::count(),
            'open_exceptions' => WarehouseException::where('status', 'open')->count(),
        ];

        $transfers = WarehouseTransfer::with(['product', 'fromWarehouse', 'toWarehouse'])->latest()->take(10)->get();
        $exceptions = WarehouseException::with('product')->latest()->take(10)->get();

        return view('organize.reports', compact('warehouses', 'metrics', 'transfers', 'exceptions'));
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseRack;

use App\Services\Warehouse\WarehouseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WarehouseLocationController extends Controller
{
    public function __construct(protected WarehouseService $warehouseService) {}

    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'type', 'status']);
        $warehouses = $this->warehouseService->getWarehouses($filters, 15);
        $managers = User::where('status', 'active')->get();
        $racks = WarehouseRack::with('aisle.zone.warehouse')->get();

        return view('organize.locations', compact('warehouses', 'managers', 'racks', 'filters'));
    }

    public function storeWarehouse(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:warehouses,code'],
            'type' => ['required', 'string'],
            'total_capacity' => ['required', 'numeric', 'min:1'],
            'capacity_unit' => ['required', 'string'],
            'city' => ['nullable', 'string'],
            'manager_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $this->warehouseService->createWarehouse($validated);

        return redirect()->route('organize.locations.index')
                         ->with('success', 'Warehouse facility created with 5-tier default zone hierarchy!');
    }

    public function storeBin(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'warehouse_rack_id' => ['required', 'integer', 'exists:warehouse_racks,id'],
            'shelf_number' => ['required', 'string'],
            'bin_number' => ['required', 'string'],
            'max_weight' => ['nullable', 'numeric'],
            'max_volume' => ['nullable', 'numeric'],
        ]);

        $bin = $this->warehouseService->createBin($validated);

        return redirect()->route('organize.locations.index')
                         ->with('success', "Storage bin {$bin->location_code} created successfully!");
    }
}

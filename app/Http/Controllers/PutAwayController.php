<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\StorageRequest;
use App\Models\Warehouse;
use App\Services\Warehouse\PutAwayService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PutAwayController extends Controller
{
    public function __construct(protected PutAwayService $putAwayService) {}

    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'priority', 'status']);
        $requests = $this->putAwayService->getPendingRequests($filters, 20);

        $selectedId = $request->get('request_id', $requests->first()?->id);
        $selectedRequest = $selectedId ? StorageRequest::with(['product', 'stockReceipt', 'assignedBin'])->find($selectedId) : null;

        $warehouses = Warehouse::with('zones.aisles.racks.bins')->where('status', 'active')->get();

        return view('organize.putaway', compact('requests', 'selectedRequest', 'warehouses', 'filters'));
    }

    public function assignLocation(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'warehouse_name' => ['nullable', 'string'],
            'rack_name' => ['nullable', 'string'],
            'shelf' => ['nullable', 'string'],
            'bin' => ['nullable', 'string'],
            'bin_id' => ['nullable', 'integer', 'exists:warehouse_bins,id'],
        ]);

        $this->putAwayService->confirmPutAway($id, $validated);

        return redirect()->route('organize.putaway.index')
                         ->with('success', 'Storage request confirmed! Warehouse coordinates generated and physical inventory updated.');
    }
}

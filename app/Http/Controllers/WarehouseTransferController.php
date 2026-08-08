<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Warehouse;
use App\Services\Warehouse\WarehouseTransferService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WarehouseTransferController extends Controller
{
    public function __construct(protected WarehouseTransferService $transferService) {}

    public function index(Request $request): View
    {
        $filters = $request->only(['search']);
        $transfers = $this->transferService->getTransfers($filters, 15);
        $products = Product::where('status', 'active')->get();
        $warehouses = Warehouse::where('status', 'active')->get();

        return view('organize.transfers', compact('transfers', 'products', 'warehouses', 'filters'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'from_coordinate' => ['required', 'string'],
            'to_coordinate' => ['required', 'string'],
            'reason' => ['nullable', 'string'],
        ]);

        $transfer = $this->transferService->createTransfer($validated);

        return redirect()->route('organize.transfers.index')
                         ->with('success', "Internal transfer #{$transfer->transfer_number} processed successfully!");
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductSerial;
use App\Repositories\Contracts\InventoryRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockBatchSerialController extends Controller
{
    /**
     * InventoryRepositoryInterface instance.
     *
     * @var InventoryRepositoryInterface
     */
    protected InventoryRepositoryInterface $inventoryRepo;

    /**
     * StockBatchSerialController constructor.
     *
     * @param InventoryRepositoryInterface $inventoryRepo
     */
    public function __construct(InventoryRepositoryInterface $inventoryRepo)
    {
        $this->inventoryRepo = $inventoryRepo;
    }

    /**
     * Display Batch Management dashboard.
     */
    public function indexBatches(): View
    {
        $batches = $this->inventoryRepo->getBatches(15);
        return view('stock.batches', compact('batches'));
    }

    /**
     * Display Serial Numbers tracking dashboard.
     */
    public function indexSerials(Request $request): View
    {
        $filters = $request->only(['search', 'status', 'product_id']);
        $serials = $this->inventoryRepo->getSerials($filters, 15);
        $products = Product::where('serial_tracking', true)->get();

        return view('stock.serials', compact('serials', 'products', 'filters'));
    }

    /**
     * Store new serial numbers manually for a product.
     */
    public function storeSerial(Request $request): RedirectResponse
    {
        $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'serial_number' => ['required', 'string', 'max:100', 'unique:product_serials,serial_number'],
            'status' => ['required', 'string', 'in:available,sold,damaged,returned'],
        ]);

        ProductSerial::create([
            'product_id' => $request->input('product_id'),
            'serial_number' => trim($request->input('serial_number')),
            'status' => $request->input('status'),
        ]);

        return back()->with('success', 'Product serial number registered successfully.');
    }
}

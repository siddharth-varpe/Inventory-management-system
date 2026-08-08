<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Stock\StockAdjustmentRequest;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Services\Contracts\ProductServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StockAdjustmentController extends Controller
{
    /**
     * ProductServiceInterface instance.
     *
     * @var ProductServiceInterface
     */
    protected ProductServiceInterface $productService;

    /**
     * StockAdjustmentController constructor.
     *
     * @param ProductServiceInterface $productService
     */
    public function __construct(ProductServiceInterface $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Display Stock Adjustments screen.
     */
    public function index(): View
    {
        $products = Product::where('status', 'active')->get();
        $adjustments = StockAdjustment::with(['product', 'creator', 'approver'])->orderByDesc('id')->paginate(15);

        return view('stock.adjustments', compact('products', 'adjustments'));
    }

    /**
     * Process stock adjustment.
     */
    public function store(StockAdjustmentRequest $request): RedirectResponse
    {
        $this->productService->adjustStock($request->validated());

        return back()->with('success', 'Stock adjustment recorded successfully.');
    }

    /**
     * Approve pending high-value adjustment.
     */
    public function approve(int $id): RedirectResponse
    {
        DB::transaction(function () use ($id) {
            /** @var StockAdjustment $adjustment */
            $adjustment = StockAdjustment::findOrFail($id);
            if ($adjustment->status !== 'pending') {
                return;
            }

            $product = $adjustment->product;
            $newStock = max(0, $product->physical_stock + $adjustment->quantity);
            $product->update([
                'physical_stock' => $newStock,
                'available_stock' => max(0, $newStock - $product->reserved_stock),
            ]);

            $adjustment->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
            ]);
        });

        return back()->with('success', 'Stock adjustment approved and inventory balance updated.');
    }

    /**
     * Reject pending high-value adjustment.
     */
    public function reject(int $id): RedirectResponse
    {
        $adjustment = StockAdjustment::findOrFail($id);
        if ($adjustment->status === 'pending') {
            $adjustment->update([
                'status' => 'rejected',
                'approved_by' => auth()->id(),
            ]);
        }

        return back()->with('success', 'Stock adjustment request rejected.');
    }
}

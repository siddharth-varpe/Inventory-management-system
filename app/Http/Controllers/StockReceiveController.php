<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Stock\ReceiveStockRequest;
use App\Models\Product;
use App\Models\StockReceipt;
use App\Services\Contracts\ProductServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StockReceiveController extends Controller
{
    /**
     * ProductServiceInterface instance.
     *
     * @var ProductServiceInterface
     */
    protected ProductServiceInterface $productService;

    /**
     * StockReceiveController constructor.
     *
     * @param ProductServiceInterface $productService
     */
    public function __construct(ProductServiceInterface $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Display Receive Stock screen.
     */
    public function index(): View
    {
        $products = Product::where('status', 'active')->get();
        $recentReceipts = StockReceipt::with('product')->orderByDesc('id')->paginate(15);

        return view('stock.receive', compact('products', 'recentReceipts'));
    }

    /**
     * Process stock receiving.
     */
    public function store(ReceiveStockRequest $request): RedirectResponse
    {
        $this->productService->receiveStock($request->validated());

        return back()->with('success', 'Supplier inventory received successfully. Physical stock and weighted cost updated.');
    }
}

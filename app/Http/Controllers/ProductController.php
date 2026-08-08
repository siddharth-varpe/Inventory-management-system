<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\AuditLog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\Tax;
use App\Models\Unit;
use App\Services\Contracts\ProductServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * ProductServiceInterface instance.
     *
     * @var ProductServiceInterface
     */
    protected ProductServiceInterface $productService;

    /**
     * ProductController constructor.
     *
     * @param ProductServiceInterface $productService
     */
    public function __construct(ProductServiceInterface $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Display Operational Product Catalog table.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'category_id', 'brand_id', 'status', 'warehouse_location', 'stock_status', 'sort_by', 'sort_dir']);
        $products = $this->productService->getCatalog($filters, 15);

        $categories = Category::all();
        $brands = Brand::all();

        return view('stock.catalog', compact('products', 'categories', 'brands', 'filters'));
    }

    /**
     * Show create product form.
     */
    public function create(): View
    {
        $categories = Category::all();
        $brands = Brand::all();
        $units = Unit::all();
        $taxes = Tax::all();
        $attributes = ProductAttribute::where('status', 'active')->orderBy('display_order')->get();

        return view('stock.products.create', compact('categories', 'brands', 'units', 'taxes', 'attributes'));
    }

    /**
     * Store new product.
     */
    public function store(StoreProductRequest $request): RedirectResponse
    {
        try {
            $product = $this->productService->createProduct(
                $request->validated(),
                $request->file('image'),
                $request->file('documents')
            );

            return redirect()->route('products.show', $product)->with('success', "Product '{$product->name}' registered successfully in master catalog.");
        } catch (\Throwable $e) {
            Log::error("ProductController::store failed: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return back()->withInput()->with('error', 'Failed to register product: ' . $e->getMessage());
        }
    }

    /**
     * Show Product Details screen with tabs.
     */
    public function show(Product $product): View
    {
        $product->load([
            'category',
            'brand',
            'unit',
            'tax',
            'inventories' => fn ($q) => $q->orderByDesc('id'),
            'receipts' => fn ($q) => $q->orderByDesc('id')->take(10),
            'adjustments' => fn ($q) => $q->orderByDesc('id')->take(10),
            'attributeValues.attribute',
        ]);

        $auditLogs = AuditLog::where('table_name', 'products')
            ->where('record_id', $product->id)
            ->orderByDesc('id')
            ->take(20)
            ->get();

        return view('stock.products.show', compact('product', 'auditLogs'));
    }

    /**
     * Show edit product form.
     */
    public function edit(Product $product): View
    {
        $product->load(['attributeValues']);
        $categories = Category::all();
        $brands = Brand::all();
        $units = Unit::all();
        $taxes = Tax::all();
        $attributes = ProductAttribute::where('status', 'active')->orderBy('display_order')->get();

        return view('stock.products.edit', compact('product', 'categories', 'brands', 'units', 'taxes', 'attributes'));
    }

    /**
     * Update product details.
     */
    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        try {
            $this->productService->updateProduct(
                $product->id,
                $request->validated(),
                $request->file('image'),
                $request->file('documents')
            );

            return redirect()->route('products.show', $product)->with('success', 'Product details updated successfully.');
        } catch (\Throwable $e) {
            Log::error("ProductController::update failed: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return back()->withInput()->with('error', 'Failed to update product: ' . $e->getMessage());
        }
    }

    /**
     * Soft delete product.
     */
    public function destroy(Product $product): RedirectResponse
    {
        try {
            $this->productService->delete($product->id);

            return redirect()->route('products.index')->with('success', 'Product removed from active catalog.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Duplicate existing product master.
     */
    public function duplicate(Product $product): RedirectResponse
    {
        $newProduct = $this->productService->duplicateProduct($product->id);

        return redirect()->route('products.show', $newProduct)->with('success', 'Product duplicated successfully with new SKU and Code.');
    }

    /**
     * Archive product.
     */
    public function archive(Product $product): RedirectResponse
    {
        $this->productService->archiveProduct($product->id);

        return back()->with('success', 'Product archived successfully.');
    }

    /**
     * Restore product.
     */
    public function restore(int|string $id): RedirectResponse
    {
        $this->productService->restoreProduct($id);

        return back()->with('success', 'Product restored to active status.');
    }
}

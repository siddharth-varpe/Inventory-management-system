<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use App\Services\Export\CsvExportService;
use App\Services\Import\CsvImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StockImportExportController extends Controller
{
    /**
     * CsvExportService instance.
     *
     * @var CsvExportService
     */
    protected CsvExportService $exportService;

    /**
     * CsvImportService instance.
     *
     * @var CsvImportService
     */
    protected CsvImportService $importService;

    /**
     * StockImportExportController constructor.
     *
     * @param CsvExportService $exportService
     * @param CsvImportService $importService
     */
    public function __construct(CsvExportService $exportService, CsvImportService $importService)
    {
        $this->exportService = $exportService;
        $this->importService = $importService;
    }

    /**
     * Display Import / Export workspace.
     */
    public function index(): View
    {
        return view('stock.import-export');
    }

    /**
     * Download products catalog CSV export.
     */
    public function export(): StreamedResponse
    {
        $headers = ['SKU', 'Product Code', 'Product Name', 'Category', 'Brand', 'Unit', 'Purchase Price', 'Cost Price', 'Selling Price', 'Physical Stock', 'Reorder Level', 'Warehouse', 'Status'];
        $products = Product::with(['category', 'brand', 'unit'])->get()->map(function ($p) {
            return [
                $p->sku,
                $p->code,
                $p->name,
                $p->category->name ?? 'Uncategorized',
                $p->brand->name ?? 'Generic',
                $p->unit->short_name ?? 'pcs',
                $p->purchase_price,
                $p->cost_price,
                $p->selling_price,
                $p->physical_stock,
                $p->reorder_level,
                $p->warehouse_location ?? 'Main Warehouse',
                $p->status,
            ];
        });

        return $this->exportService->download('products_master_export_' . date('Ymd_His') . '.csv', $headers, $products);
    }

    /**
     * Process products CSV import.
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'import_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $rows = $this->importService->parse($request->file('import_file'));
        $createdCount = 0;
        $updatedCount = 0;

        DB::transaction(function () use ($rows, &$createdCount, &$updatedCount) {
            foreach ($rows as $row) {
                $name = trim($row['name'] ?? $row['Product Name'] ?? $row['product_name'] ?? '');
                if (empty($name)) {
                    continue;
                }

                $sku = trim($row['sku'] ?? $row['SKU'] ?? '');
                if (empty($sku)) {
                    $sku = 'SKU-' . strtoupper(Str::random(8));
                }

                $categoryName = trim($row['category'] ?? $row['Category'] ?? '');
                $brandName = trim($row['brand'] ?? $row['Brand'] ?? '');

                $category = !empty($categoryName) ? Category::firstOrCreate(['name' => $categoryName], ['slug' => Str::slug($categoryName)]) : null;
                $brand = !empty($brandName) ? Brand::firstOrCreate(['name' => $brandName], ['slug' => Str::slug($brandName)]) : null;

                $productData = [
                    'name' => $name,
                    'code' => trim($row['code'] ?? $row['Product Code'] ?? ('PRD-' . strtoupper(Str::random(6)))),
                    'barcode' => trim($row['barcode'] ?? $row['Barcode'] ?? ('890' . rand(100000000, 999999999))),
                    'category_id' => $category?->id,
                    'brand_id' => $brand?->id,
                    'purchase_price' => (float) ($row['purchase_price'] ?? $row['Purchase Price'] ?? 0),
                    'cost_price' => (float) ($row['cost_price'] ?? $row['Cost Price'] ?? 0),
                    'selling_price' => (float) ($row['selling_price'] ?? $row['Selling Price'] ?? 0),
                    'physical_stock' => (int) ($row['physical_stock'] ?? $row['Physical Stock'] ?? 0),
                    'reorder_level' => (int) ($row['reorder_level'] ?? $row['Reorder Level'] ?? 10),
                    'warehouse_location' => trim($row['warehouse'] ?? $row['Warehouse'] ?? 'Main Warehouse'),
                    'status' => 'active',
                ];

                $existing = Product::where('sku', $sku)->first();
                if ($existing) {
                    $existing->update($productData);
                    $updatedCount++;
                } else {
                    $productData['sku'] = $sku;
                    $productData['created_by'] = auth()->id();
                    Product::create($productData);
                    $createdCount++;
                }
            }
        });

        return back()->with('success', "Import completed cleanly: {$createdCount} products created, {$updatedCount} products updated.");
    }
}

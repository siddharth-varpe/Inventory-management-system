<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Stock\OpeningStockRequest;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\StockReceipt;
use App\Services\Import\CsvImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OpeningStockController extends Controller
{
    /**
     * Display Opening Stock entry screen.
     */
    public function index(): View
    {
        $products = Product::where('status', 'active')->get();
        $history = StockReceipt::where('type', 'opening_stock')
            ->with(['product', 'creator'])
            ->orderByDesc('id')
            ->paginate(15);

        return view('stock.opening-stock', compact('products', 'history'));
    }

    /**
     * Store initial stock entry for a single product.
     */
    public function store(OpeningStockRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $product = Product::findOrFail($data['product_id']);
            $qty = (int) $data['quantity'];
            $unitCost = (float) $data['unit_cost'];
            $totalCost = $qty * $unitCost;

            // 1. Create StockReceipt marked as opening_stock
            StockReceipt::create([
                'reference_no' => 'OPN-' . strtoupper(Str::random(8)),
                'supplier_name' => 'Initial Opening Balance',
                'product_id' => $product->id,
                'type' => 'opening_stock',
                'quantity' => $qty,
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,
                'batch_number' => $data['batch_number'] ?? 'OPN-LOT-01',
                'mfg_date' => $data['mfg_date'] ?? null,
                'expiry_date' => $data['expiry_date'] ?? null,
                'notes' => $data['notes'] ?? 'Initial Opening Stock Entry',
                'created_by' => auth()->id(),
            ]);

            // 2. Create Inventory Lot
            Inventory::create([
                'product_id' => $product->id,
                'batch_number' => $data['batch_number'] ?? 'OPN-LOT-01',
                'lot_number' => 'LOT-' . rand(1000, 9999),
                'quantity' => $qty,
                'unit_cost' => $unitCost,
                'selling_price' => $product->selling_price,
                'mfg_date' => $data['mfg_date'] ?? null,
                'expiry_date' => $data['expiry_date'] ?? null,
                'storage_condition' => $product->storage_condition,
                'status' => 'active',
            ]);

            // 3. Update Product Physical Balance & Weighted Cost
            $currentStock = $product->physical_stock;
            $currentCost = (float) $product->cost_price;
            $newStock = $currentStock + $qty;
            $newAvgCost = $newStock > 0 ? (($currentStock * $currentCost) + $totalCost) / $newStock : $unitCost;

            $product->update([
                'physical_stock' => $newStock,
                'available_stock' => max(0, $newStock - $product->reserved_stock),
                'cost_price' => $newAvgCost,
                'warehouse_location' => $data['warehouse_location'] ?? $product->warehouse_location,
                'rack_location' => $data['rack_location'] ?? $product->rack_location,
            ]);
        });

        return back()->with('success', 'Opening stock registered successfully.');
    }

    /**
     * Process bulk opening stock upload via CSV.
     */
    public function bulkUpload(Request $request, CsvImportService $importService): RedirectResponse
    {
        $request->validate([
            'opening_stock_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $rows = $importService->parse($request->file('opening_stock_file'));
        $importedCount = 0;

        DB::transaction(function () use ($rows, &$importedCount) {
            foreach ($rows as $row) {
                $sku = trim($row['sku'] ?? $row['SKU'] ?? '');
                $qty = (int) ($row['quantity'] ?? $row['Quantity'] ?? 0);
                $cost = (float) ($row['unit_cost'] ?? $row['Cost'] ?? 0);

                if (empty($sku) || $qty <= 0) {
                    continue;
                }

                $product = Product::where('sku', $sku)->orWhere('code', $sku)->first();
                if (!$product) {
                    continue;
                }

                $totalCost = $qty * $cost;

                StockReceipt::create([
                    'reference_no' => 'OPN-' . strtoupper(Str::random(8)),
                    'supplier_name' => 'Bulk Opening Upload',
                    'product_id' => $product->id,
                    'type' => 'opening_stock',
                    'quantity' => $qty,
                    'unit_cost' => $cost,
                    'total_cost' => $totalCost,
                    'batch_number' => 'OPN-BULK',
                    'notes' => 'Bulk Opening Stock CSV Import',
                    'created_by' => auth()->id(),
                ]);

                Inventory::create([
                    'product_id' => $product->id,
                    'batch_number' => 'OPN-BULK',
                    'lot_number' => 'LOT-' . rand(1000, 9999),
                    'quantity' => $qty,
                    'unit_cost' => $cost,
                    'selling_price' => $product->selling_price,
                    'status' => 'active',
                ]);

                $newStock = $product->physical_stock + $qty;
                $product->update([
                    'physical_stock' => $newStock,
                    'available_stock' => max(0, $newStock - $product->reserved_stock),
                    'cost_price' => $cost,
                ]);

                $importedCount++;
            }
        });

        return back()->with('success', "Bulk opening stock imported for {$importedCount} products.");
    }
}

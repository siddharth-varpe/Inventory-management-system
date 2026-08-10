<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;

use App\Services\Export\CsvExportService;
use App\Services\Report\ReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StockReportController extends Controller
{
    /**
     * ReportService instance.
     *
     * @var ReportService
     */
    protected ReportService $reportService;

    /**
     * CsvExportService instance.
     *
     * @var CsvExportService
     */
    protected CsvExportService $exportService;

    /**
     * StockReportController constructor.
     *
     * @param ReportService $reportService
     * @param CsvExportService $exportService
     */
    public function __construct(ReportService $reportService, CsvExportService $exportService)
    {
        $this->reportService = $reportService;
        $this->exportService = $exportService;
    }

    /**
     * Display Inventory Analytics & Reports workspace.
     */
    public function index(Request $request): View
    {
        $activeReport = $request->input('tab', 'valuation');

        $metrics = [
            'total_inventory_value' => Product::all()->sum(fn ($p) => $p->physical_stock * $p->cost_price),
            'total_selling_value' => Product::all()->sum(fn ($p) => $p->physical_stock * $p->selling_price),
            'low_stock_count' => Product::lowStock()->count(),
            'dead_stock_count' => $this->reportService->getDeadStock()->count(),
        ];

        $reportData = match ($activeReport) {
            'valuation' => $this->reportService->getInventoryValuation(),
            'ledger' => $this->reportService->getStockLedger(),
            'low_stock' => $this->reportService->getLowStock(),
            'dead_stock' => $this->reportService->getDeadStock(),
            'fast_moving' => $this->reportService->getFastMoving(),
            'slow_moving' => $this->reportService->getSlowMoving(),
            'adjustments' => $this->reportService->getAdjustmentReport(),
            'expiry' => $this->reportService->getExpiryReport(),
            default => Product::with(['category', 'brand', 'unit'])->get(),
        };

        return view('stock.reports', compact('metrics', 'activeReport', 'reportData'));
    }

    /**
     * Export designated report to CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $type = $request->input('type', 'valuation');

        switch ($type) {
            case 'valuation':
                $headers = ['SKU', 'Product Name', 'Category', 'Physical Stock', 'Cost Price', 'Selling Price', 'Total Cost Value', 'Total Selling Value'];
                $valData = $this->reportService->getInventoryValuation()['products'];
                $rows = $valData->map(fn ($p) => [
                    $p->sku, $p->name, $p->category->name ?? 'N/A', $p->physical_stock,
                    $p->cost_price, $p->selling_price, $p->physical_stock * $p->cost_price, $p->physical_stock * $p->selling_price
                ]);
                return $this->exportService->download('inventory_valuation_report.csv', $headers, $rows);

            case 'ledger':
                $headers = ['Date', 'Reference No', 'Product Name', 'SKU', 'Movement Type', 'Quantity', 'Unit Cost', 'Total Amount', 'User'];
                $ledger = $this->reportService->getStockLedger();
                $rows = $ledger->map(fn ($l) => [
                    $l['date']->format('Y-m-d H:i:s'), $l['reference_no'], $l['product_name'], $l['sku'], $l['type'], $l['quantity'], $l['unit_cost'], $l['total_amount'], $l['user']
                ]);
                return $this->exportService->download('stock_ledger_report.csv', $headers, $rows);

            case 'low_stock':
                $headers = ['SKU', 'Product Name', 'Category', 'Warehouse', 'Current Stock', 'Min Stock', 'Reorder Level'];
                $lowStock = $this->reportService->getLowStock();
                $rows = $lowStock->map(fn ($p) => [
                    $p->sku, $p->name, $p->category->name ?? 'N/A', $p->warehouse_location ?? 'Main', $p->physical_stock, $p->min_stock, $p->reorder_level
                ]);
                return $this->exportService->download('low_stock_report.csv', $headers, $rows);

            case 'adjustments':
                $headers = ['Reference No', 'Product Name', 'SKU', 'Adjustment Type', 'Quantity', 'Unit Cost', 'Total Amount', 'Reason', 'Status'];
                $adj = $this->reportService->getAdjustmentReport();
                $rows = $adj->map(fn ($a) => [
                    $a->reference_no, $a->product->name ?? 'N/A', $a->product->sku ?? 'N/A', $a->type, $a->quantity, $a->unit_cost, $a->total_amount, $a->reason, $a->status
                ]);
                return $this->exportService->download('stock_adjustment_report.csv', $headers, $rows);

            default:
                $headers = ['SKU', 'Product Code', 'Name', 'Category', 'Brand', 'Physical Stock', 'Cost Price', 'Selling Price'];
                $products = Product::with(['category', 'brand'])->get();
                $rows = $products->map(fn ($p) => [
                    $p->sku, $p->code, $p->name, $p->category->name ?? 'N/A', $p->brand->name ?? 'N/A', $p->physical_stock, $p->cost_price, $p->selling_price
                ]);
                return $this->exportService->download('inventory_products_report.csv', $headers, $rows);
        }
    }
}

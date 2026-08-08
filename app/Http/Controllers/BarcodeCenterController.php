<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\Barcode\BarcodeService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BarcodeCenterController extends Controller
{
    /**
     * BarcodeService instance.
     *
     * @var BarcodeService
     */
    protected BarcodeService $barcodeService;

    /**
     * BarcodeCenterController constructor.
     *
     * @param BarcodeService $barcodeService
     */
    public function __construct(BarcodeService $barcodeService)
    {
        $this->barcodeService = $barcodeService;
    }

    /**
     * Display Barcode & QR Code Center.
     */
    public function index(Request $request): View
    {
        $products = Product::where('status', 'active')->get();
        $selectedProduct = null;
        $barcodeHTML = null;
        $qrSVG = null;

        if ($request->filled('product_id')) {
            $selectedProduct = Product::find($request->input('product_id'));
            if ($selectedProduct) {
                $code = $selectedProduct->barcode ?: $selectedProduct->sku;
                $barcodeHTML = $this->barcodeService->generateBarcodeHTML($code, 2, 50);
                $qrSVG = $this->barcodeService->generateQRCodeSVG($selectedProduct->sku, 120);
            }
        }

        return view('stock.barcodes', compact('products', 'selectedProduct', 'barcodeHTML', 'qrSVG'));
    }

    /**
     * Print Label Sheet for single or multiple products.
     */
    public function printLabels(Request $request): View
    {
        $productIds = $request->input('product_ids', []);
        $quantities = $request->input('quantities', []);

        $labels = [];

        if (!empty($productIds) && is_array($productIds)) {
            foreach ($productIds as $idx => $pid) {
                $product = Product::find($pid);
                if ($product) {
                    $count = isset($quantities[$idx]) ? max(1, (int) $quantities[$idx]) : 1;
                    $code = $product->barcode ?: $product->sku;
                    $svg = $this->barcodeService->generateBarcodeHTML($code, 2, 45);

                    for ($i = 0; $i < $count; $i++) {
                        $labels[] = [
                            'product' => $product,
                            'barcode_svg' => $svg,
                        ];
                    }
                }
            }
        } elseif ($request->filled('single_product_id')) {
            $product = Product::find($request->input('single_product_id'));
            if ($product) {
                $count = max(1, (int) $request->input('copy_count', 12));
                $code = $product->barcode ?: $product->sku;
                $svg = $this->barcodeService->generateBarcodeHTML($code, 2, 45);

                for ($i = 0; $i < $count; $i++) {
                    $labels[] = [
                        'product' => $product,
                        'barcode_svg' => $svg,
                    ];
                }
            }
        }

        return view('stock.print-labels', compact('labels'));
    }
}

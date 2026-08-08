<?php

declare(strict_types=1);

namespace App\Modules\Sales\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\PickingTask;
use App\Models\TransportRequest;
use App\Domain\Sales\QuotationService;
use App\Domain\Sales\CustomerPricingService;
use App\Domain\Communication\CommunicationEngineService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class QuotationController extends Controller
{
    public function __construct(
        protected QuotationService $quotationService,
        protected CustomerPricingService $pricingService,
        protected CommunicationEngineService $cceService
    ) {}

    /**
     * Interactive 3-Panel Sales Workspace
     */
    public function workspace(Request $request): View
    {
        $productsQuery = Product::with(['category', 'brand', 'unit', 'tax'])->where('status', 'active');

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $productsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $productsQuery->where('category_id', $request->input('category_id'));
        }
        if ($request->filled('brand_id')) {
            $productsQuery->where('brand_id', $request->input('brand_id'));
        }

        $products = $productsQuery->paginate(12)->withQueryString();

        $categories = Category::all();
        $brands = Brand::all();
        $customers = Customer::where('status', 'active')->with('addresses')->get();

        return view('sales.workspace', compact('products', 'categories', 'brands', 'customers'));
    }

    /**
     * Quotations Queue Datatable
     */
    public function index(Request $request): View
    {
        $query = Quotation::with(['customer', 'salesperson', 'salesOrder']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where('quotation_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($q) use ($search) {
                      $q->where('company_name', 'like', "%{$search}%");
                  });
        }

        $quotations = $query->latest()->paginate(15)->withQueryString();

        return view('sales.quotations.index', compact('quotations'));
    }

    /**
     * Store Quotation from Sales Cart
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'validity_days' => 'nullable|integer|min:1',
            'delivery_terms' => 'nullable|string',
            'payment_terms' => 'nullable|string',
            'remarks' => 'nullable|string',
            'cart_data' => 'required|string', // JSON array of [{product_id, quantity, unit_price, discount_amount, discount_type}]
        ]);

        $customer = Customer::findOrFail($validated['customer_id']);
        $cartItems = json_decode($validated['cart_data'], true);

        if (empty($cartItems)) {
            return redirect()->back()->with('error', 'Sales Cart is empty. Please add products to cart before generating quotation.');
        }

        $quotation = $this->quotationService->createQuotation(
            $customer,
            auth()->id() ?? 1,
            $cartItems,
            [
                'validity_days' => $validated['validity_days'] ?? 30,
                'delivery_terms' => $validated['delivery_terms'] ?? null,
                'payment_terms' => $validated['payment_terms'] ?? null,
                'remarks' => $validated['remarks'] ?? null,
            ]
        );

        return redirect()->route('sales.quotations.show', $quotation->id)
            ->with('success', "Quotation {$quotation->quotation_number} generated & prepared for Customer Communication (CCE)! Status: " . strtoupper($quotation->status));
    }

    /**
     * Duplicate Quotation into a New Draft
     */
    public function duplicate(Quotation $quotation): RedirectResponse
    {
        $newQuotation = $this->quotationService->duplicateQuotation($quotation, auth()->id() ?? 1);

        return redirect()->route('sales.quotations.show', $newQuotation->id)
            ->with('success', "Quotation {$quotation->quotation_number} duplicated into new Draft Quotation {$newQuotation->quotation_number} with CCE Revision 2.0.");
    }

    /**
     * Quotation 360° Profile & Detail Workspace
     */
    public function show(Quotation $quotation): View
    {
        $quotation->load(['customer.addresses', 'salesperson', 'approvedBy', 'items.product.category', 'items.product.unit', 'salesOrder']);

        $pickingTask = null;
        $transportRequest = null;

        if ($quotation->salesOrder) {
            $pickingTask = PickingTask::with(['items.product', 'items.sourceBin', 'assignedUser', 'warehouse'])
                ->where('order_reference', $quotation->salesOrder->order_number)
                ->first();

            $transportRequest = TransportRequest::with(['assignedDriver'])
                ->where('sales_order_id', $quotation->salesOrder->id)
                ->first();
        }

        $cceHistory = $this->cceService->getCommunicationHistory('Quotation', $quotation->id);
        $cceRecord = $cceHistory->first();

        return view('sales.quotations.show', compact('quotation', 'pickingTask', 'transportRequest', 'cceRecord', 'cceHistory'));
    }

    /**
     * Real-time Live Status Synchronization Endpoint (Single Source of Truth)
     */
    public function liveStatus(Quotation $quotation): JsonResponse
    {
        $quotation->load(['salesOrder']);

        $pickingTask = null;
        $transportRequest = null;

        if ($quotation->salesOrder) {
            $pickingTask = PickingTask::with(['items.product', 'items.sourceBin', 'assignedUser', 'warehouse'])
                ->where('order_reference', $quotation->salesOrder->order_number)
                ->first();

            $transportRequest = TransportRequest::with(['assignedDriver'])
                ->where('sales_order_id', $quotation->salesOrder->id)
                ->first();
        }

        $warehouseData = null;
        if ($pickingTask) {
            $itemsData = $pickingTask->items->map(function ($item) {
                $isVerified = (bool)$item->is_verified || ((int)$item->picked_quantity > 0 && (int)$item->picked_quantity >= (int)$item->requested_quantity);
                return [
                    'id' => $item->id,
                    'sku' => $item->product->sku ?? 'N/A',
                    'product_name' => $item->product->name ?? 'Item',
                    'source_bin' => $item->sourceBin->code ?? 'BIN-MAIN',
                    'requested_quantity' => (int)$item->requested_quantity,
                    'picked_quantity' => (int)$item->picked_quantity,
                    'is_verified' => $isVerified,
                ];
            });

            $warehouseData = [
                'task_number' => $pickingTask->task_number,
                'status' => $pickingTask->status,
                'status_label' => ucfirst(str_replace('_', ' ', $pickingTask->status)),
                'priority' => ucfirst($pickingTask->priority),
                'operator_name' => $pickingTask->assignedUser->name ?? 'Unassigned Operator',
                'warehouse_name' => $pickingTask->warehouse->name ?? 'Main Distribution Center',
                'total_items' => $pickingTask->total_items_count,
                'verified_items' => $pickingTask->verified_items_count,
                'completion_percentage' => $pickingTask->completion_percentage,
                'progress_color' => $pickingTask->progress_color_class,
                'updated_at' => $pickingTask->updated_at ? $pickingTask->updated_at->format('d M Y, h:i A') : 'N/A',
                'items' => $itemsData,
            ];
        }

        $transportData = null;
        if ($transportRequest) {
            $transportData = [
                'request_number' => $transportRequest->request_number,
                'status' => $transportRequest->status,
                'status_label' => ucfirst(str_replace('_', ' ', $transportRequest->status)),
                'carrier' => $transportRequest->carrier ?? 'Internal Logistics Fleet',
                'vehicle_number' => $transportRequest->vehicle_number ?? 'Waiting for Transport Dept',
                'driver_name' => $transportRequest->driver_name ?? 'Unassigned Driver',
                'tracking_number' => $transportRequest->tracking_number ?? 'PENDING-DISPATCH',
                'delivery_address' => $transportRequest->delivery_address ?? 'Primary Customer Destination Site',
                'completion_percentage' => $transportRequest->completion_percentage,
                'progress_color' => $transportRequest->progress_color_class,
                'dispatched_at' => $transportRequest->dispatched_at ? $transportRequest->dispatched_at->format('d M Y, h:i A') : 'Awaiting Dispatch',
                'delivered_at' => $transportRequest->delivered_at ? $transportRequest->delivered_at->format('d M Y, h:i A') : 'In Transit',
            ];
        }

        return response()->json([
            'success' => true,
            'quotation_id' => $quotation->id,
            'quotation_status' => $quotation->status,
            'sales_order' => $quotation->salesOrder ? [
                'order_number' => $quotation->salesOrder->order_number,
                'status' => ucfirst($quotation->salesOrder->status),
            ] : null,
            'warehouse' => $warehouseData,
            'transport' => $transportData,
        ]);
    }

    /**
     * Approve Quotation
     */
    public function approve(Quotation $quotation): RedirectResponse
    {
        $this->quotationService->approveQuotation($quotation, auth()->id() ?? 1);

        return redirect()->route('sales.quotations.show', $quotation->id)
            ->with('success', "Quotation {$quotation->quotation_number} approved successfully.");
    }

    /**
     * Reject Quotation
     */
    public function reject(Request $request, Quotation $quotation): RedirectResponse
    {
        $request->validate(['reason' => 'required|string|max:255']);

        $this->quotationService->rejectQuotation($quotation, auth()->id() ?? 1, $request->input('reason'));

        return redirect()->route('sales.quotations.show', $quotation->id)
            ->with('success', "Quotation {$quotation->quotation_number} rejected.");
    }

    /**
     * Commercial PDF View
     */
    public function pdf(Quotation $quotation): View
    {
        $quotation->load(['customer.addresses', 'salesperson', 'items.product.category']);
        $company = \App\Models\Company::first();

        return view('sales.quotations.pdf', compact('quotation', 'company'));
    }
}

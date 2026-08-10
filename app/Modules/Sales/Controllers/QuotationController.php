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
use App\Services\Contracts\ProductServiceInterface;

class QuotationController extends Controller
{
    public function __construct(
        protected QuotationService $quotationService,
        protected CustomerPricingService $pricingService,
        protected CommunicationEngineService $cceService,
        protected ProductServiceInterface $productService
    ) {}

    /**
     * Interactive 3-Panel Sales Workspace
     */
    public function workspace(Request $request): View
    {
        $filters = $request->only(['search', 'category_id', 'brand_id', 'status', 'stock_status', 'sort_by', 'sort_dir']);
        if (!isset($filters['status'])) {
            $filters['status'] = 'active';
        }

        $products = $this->productService->getCatalog($filters, 15);

        $categories = Category::all();
        $brands = Brand::all();
        $customers = Customer::where('status', 'active')->with('addresses')->get();

        return view('sales.workspace', compact('products', 'categories', 'brands', 'customers', 'filters'));
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

        try {
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
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("QuotationController store exception: " . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Unable to create quotation. Please check item stock availability and try again.');
        }
    }

    /**
     * Edit Quotation Workspace
     */
    public function edit(Quotation $quotation)
    {
        if ($quotation->status === 'converted' || $quotation->sales_order_id !== null) {
            return redirect()->route('sales.quotations.show', $quotation->id)
                ->with('error', "Quotation #{$quotation->quotation_number} has already been converted to a Sales Order and cannot be edited.");
        }

        $quotation->load(['customer', 'items.product.category', 'items.product.unit', 'items.product.tax']);

        $products = $this->productService->getCatalog(['status' => 'active'], 100);
        $categories = Category::all();
        $brands = Brand::all();
        $customers = Customer::where('status', 'active')->with('addresses')->get();

        return view('sales.quotations.edit', compact('quotation', 'products', 'categories', 'brands', 'customers'));
    }

    /**
     * Update Existing Quotation
     */
    public function update(Request $request, Quotation $quotation): RedirectResponse
    {
        if ($quotation->status === 'converted' || $quotation->sales_order_id !== null) {
            return redirect()->route('sales.quotations.show', $quotation->id)
                ->with('error', "Quotation #{$quotation->quotation_number} has already been converted to a Sales Order and cannot be edited.");
        }

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'validity_days' => 'nullable|integer|min:1',
            'delivery_terms' => 'nullable|string',
            'payment_terms' => 'nullable|string',
            'remarks' => 'nullable|string',
            'cart_data' => 'required|string',
        ]);

        $customer = Customer::findOrFail($validated['customer_id']);
        $cartItems = json_decode($validated['cart_data'], true);

        if (empty($cartItems)) {
            return redirect()->back()->with('error', 'Quotation must have at least one product item.');
        }

        try {
            $updatedQuotation = $this->quotationService->updateQuotation(
                $quotation,
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

            return redirect()->route('sales.quotations.show', $updatedQuotation->id)
                ->with('success', "Quotation {$updatedQuotation->quotation_number} updated successfully!");
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("QuotationController update error for #{$quotation->quotation_number}: " . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Unable to update quotation. Please check item stock availability and try again.');
        }
    }

    /**
     * Delete Quotation
     */
    public function destroy(Quotation $quotation): RedirectResponse
    {
        if ($quotation->status === 'converted' || $quotation->sales_order_id !== null) {
            return redirect()->route('sales.quotations.show', $quotation->id)
                ->with('error', "Quotation #{$quotation->quotation_number} has already been converted to a Sales Order and cannot be edited.");
        }

        try {
            $qNum = $quotation->quotation_number;
            $this->quotationService->deleteQuotation($quotation);
            return redirect()->route('sales.quotations.index')
                ->with('success', "Quotation #{$qNum} deleted successfully.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Search Canonical Product Catalog via API (JSON)
     */
    public function searchProducts(Request $request): JsonResponse
    {
        $filters = [
            'search' => $request->input('q'),
            'status' => 'active',
        ];

        $paginator = $this->productService->getCatalog($filters, 30);

        $products = collect($paginator->items())->map(function ($p) {
            $available = max(0, (int)$p->physical_stock - (int)($p->reserved_stock ?? 0));
            return [
                'id' => $p->id,
                'code' => $p->code ?? "PRD-{$p->id}",
                'sku' => $p->sku,
                'name' => $p->name,
                'category_name' => $p->category->name ?? 'General',
                'brand_name' => $p->brand->name ?? 'Generic',
                'unit_code' => $p->unit->code ?? 'Pcs',
                'selling_price' => (float)$p->selling_price,
                'gst_rate' => (float)($p->tax->rate ?? 18.00),
                'physical_stock' => (int)$p->physical_stock,
                'reserved_stock' => (int)($p->reserved_stock ?? 0),
                'available_stock' => $available,
                'is_out_of_stock' => ($available <= 0),
            ];
        });

        return response()->json([
            'success' => true,
            'count' => count($products),
            'products' => $products,
        ]);
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

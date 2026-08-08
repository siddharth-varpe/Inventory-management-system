<?php

declare(strict_types=1);

namespace App\Modules\Procurement\Controllers;

use App\Domain\Procurement\ProcurementOrchestratorService;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    public function __construct(
        protected ProcurementOrchestratorService $procurementEngine
    ) {}

    public function index(): View
    {
        $orders = PurchaseOrder::with(['supplier', 'items.product', 'createdBy', 'receivedBy'])->orderByDesc('id')->paginate(15);
        $suppliers = Supplier::where('status', 'active')->get();
        $products = Product::where('status', 'active')->get();

        return view('procurement.purchase-orders', compact('orders', 'suppliers', 'products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_cost' => ['required', 'numeric', 'min:0'],
        ]);

        $this->procurementEngine->createPurchaseOrder(
            supplierId: (int) $validated['supplier_id'],
            createdBy: auth()->id() ?? 1,
            items: [
                [
                    'product_id' => (int) $validated['product_id'],
                    'quantity' => (int) $validated['quantity'],
                    'unit_cost' => (float) $validated['unit_cost'],
                ],
            ]
        );

        return back()->with('success', 'Purchase Order contract issued and registered in Pending Dispatch state.');
    }

    public function dispatchShipment(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'lead_time_days' => ['nullable', 'integer', 'min:1'],
            'carrier_name' => ['nullable', 'string'],
            'tracking_reference' => ['nullable', 'string'],
            'vehicle_number' => ['nullable', 'string'],
        ]);

        $userId = auth()->id() ?? 1;

        try {
            $po = $this->procurementEngine->dispatchShipment($id, $userId, $validated);

            return back()->with('success', "Shipment for PO {$po->po_number} marked IN TRANSIT. Expected delivery: {$po->expected_delivery_date?->format('M d, Y')}");
        } catch (\Exception $e) {
            return back()->with('error', 'Shipment dispatch failed: ' . $e->getMessage());
        }
    }

    public function markArrived(Request $request, int $id): RedirectResponse
    {
        $userId = auth()->id() ?? 1;

        try {
            $po = $this->procurementEngine->markShipmentArrived($id, $userId);

            return back()->with('success', "Shipment for PO {$po->po_number} marked ARRIVED and dispatched to Goods Receipt Queue.");
        } catch (\Exception $e) {
            return back()->with('error', 'Shipment arrival failed: ' . $e->getMessage());
        }
    }
}

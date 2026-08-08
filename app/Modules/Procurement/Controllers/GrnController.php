<?php

declare(strict_types=1);

namespace App\Modules\Procurement\Controllers;

use App\Domain\Procurement\ProcurementOrchestratorService;
use App\Http\Controllers\Controller;
use App\Models\GoodsReceiptNote;
use App\Models\PurchaseOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GrnController extends Controller
{
    public function __construct(
        protected ProcurementOrchestratorService $procurementEngine
    ) {}

    public function index(): View
    {
        // Strict Inbound Queue Query: Only POs with shipment_status = 'arrived' (or 'completed' for partials) with remaining stock
        $orders = PurchaseOrder::with(['supplier', 'items.product'])
            ->whereIn('status', ['submitted', 'approved', 'sent', 'partial_received'])
            ->whereIn('shipment_status', ['arrived', 'completed'])
            ->get()
            ->filter(function ($po) {
                $ordered = $po->items->sum('quantity_ordered');
                $received = $po->items->sum('quantity_received');
                return ($ordered - $received) > 0;
            });

        // Completed GRN History
        $grnHistory = GoodsReceiptNote::with(['purchaseOrder', 'supplier', 'receivedBy'])
            ->orderByDesc('id')
            ->take(20)
            ->get();

        return view('procurement.grn', compact('orders', 'grnHistory'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'po_id' => ['required', 'exists:purchase_orders,id'],
            'received_qty' => ['nullable', 'integer', 'min:1'],
        ]);

        $po = PurchaseOrder::with('items')->findOrFail($validated['po_id']);

        $itemsReceived = null;
        if (!empty($validated['received_qty'])) {
            $firstItem = $po->items->first();
            if ($firstItem) {
                $itemsReceived = [$firstItem->id => (int)$validated['received_qty']];
            }
        }

        $grn = $this->procurementEngine->logGoodsReceipt(
            poId: (int) $validated['po_id'],
            receivedBy: auth()->id() ?? 1,
            warehouseId: 1,
            challanNo: 'DC-' . strtoupper(uniqid()),
            itemsReceived: $itemsReceived
        );

        return back()->with('success', "Goods Receipt Note {$grn->grn_number} recorded. WAC updated, inventory increased, and automated Put-Away task generated.");
    }
}

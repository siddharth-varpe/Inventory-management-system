<?php

declare(strict_types=1);

namespace App\Modules\Procurement\Controllers;

use App\Domain\Procurement\ProcurementOrchestratorService;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PurchaseRequisition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RequisitionController extends Controller
{
    public function __construct(
        protected ProcurementOrchestratorService $procurementEngine
    ) {}

    public function index(Request $request): View
    {
        $requisitions = PurchaseRequisition::with(['items.product', 'approvedBy', 'rejectedBy'])->orderByDesc('id')->paginate(15);
        $selectedId = $request->get('selected');
        $selectedPR = $selectedId ? PurchaseRequisition::with(['items.product', 'approvedBy', 'rejectedBy'])->find($selectedId) : $requisitions->first();
        $products = Product::where('status', 'active')->get();

        return view('procurement.requisitions', compact('requisitions', 'selectedPR', 'products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'priority' => ['required', 'string'],
            'purpose' => ['nullable', 'string'],
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $product = Product::findOrFail($validated['product_id']);

        $pr = $this->procurementEngine->createRequisition(
            requestedBy: auth()->id() ?? 1,
            departmentId: null,
            priority: $validated['priority'],
            purpose: $validated['purpose'] ?? 'General Stock Replenishment',
            items: [
                [
                    'product_id' => $product->id,
                    'quantity' => (int) $validated['quantity'],
                    'estimated_unit_cost' => (float) $product->cost_price,
                ],
            ]
        );

        return redirect()->route('procurement.requisitions.index', ['selected' => $pr->id])
            ->with('success', "Purchase Requisition {$pr->requisition_number} created and submitted for approval.");
    }

    public function approve(Request $request, int $id): RedirectResponse
    {
        $userId = auth()->id() ?? 1;

        try {
            $result = $this->procurementEngine->approveRequisition($id, $userId);

            return redirect()->to($result['route'])->with('success', $result['message']);
        } catch (\Exception $e) {
            return back()->with('error', 'Approval failed: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:3'],
            'comments' => ['nullable', 'string'],
        ]);

        $userId = auth()->id() ?? 1;

        try {
            $pr = $this->procurementEngine->rejectRequisition($id, $userId, $validated['rejection_reason'], $validated['comments']);

            return redirect()->route('procurement.requisitions.index', ['selected' => $pr->id])
                ->with('success', "Purchase Requisition {$pr->requisition_number} has been rejected.");
        } catch (\Exception $e) {
            return back()->with('error', 'Rejection failed: ' . $e->getMessage());
        }
    }
}

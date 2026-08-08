<?php

declare(strict_types=1);

namespace App\Modules\OrganizeStock\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PickingTask;
use App\Domain\Warehouse\FulfillmentStationEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FulfillmentStationController extends Controller
{
    public function __construct(
        protected FulfillmentStationEngine $fulfillmentEngine
    ) {}

    /**
     * Unified Pick & Pack Fulfillment Station Workspace View
     */
    public function index(Request $request): View
    {
        $statusFilter = $request->input('status');
        $search = $request->input('search');
        $selectedId = $request->get('task_id');
        $selectedTask = null;

        if ($selectedId) {
            $selectedTask = PickingTask::with(['items.product', 'items.sourceBin', 'warehouse', 'assignedUser'])
                ->find($selectedId);

            // Infer status filter from selected task if not explicitly supplied in request
            if (!$statusFilter && $selectedTask) {
                if ($selectedTask->status === 'completed') {
                    $statusFilter = 'completed';
                } elseif (in_array($selectedTask->status, ['picking', 'picked'])) {
                    $statusFilter = 'picking';
                }
            }
        }

        $query = PickingTask::with(['items.product', 'items.sourceBin', 'warehouse', 'assignedUser']);

        if ($statusFilter) {
            $query->where('status', $statusFilter);
        } else {
            // Default active pending/in-progress tasks queue
            $query->whereIn('status', ['pending', 'assigned', 'picking', 'picked', 'packed']);
        }

        if ($search) {
            $s = trim($search);
            $query->where(function ($q) use ($s) {
                $q->where('task_number', 'like', "%{$s}%")
                  ->orWhere('order_reference', 'like', "%{$s}%")
                  ->orWhere('customer_name', 'like', "%{$s}%");
            });
        }

        // Sort by Priority (urgent > high > medium > low) -> FIFO (created_at asc)
        $tasks = $query->orderByRaw("FIELD(priority, 'urgent', 'high', 'medium', 'low')")
                       ->orderBy('created_at', 'asc')
                       ->paginate(15)
                       ->withQueryString();

        // Fallback to highest priority task in queue if requested task ID is not found
        if (!$selectedTask && $tasks->isNotEmpty()) {
            $selectedTask = $tasks->first();
        }

        if ($selectedTask && $selectedTask->status !== 'completed') {
            // Lock active task to current operator when opened
            $this->fulfillmentEngine->lockTask($selectedTask, auth()->id() ?? 1);
        }

        return view('organize-stock.fulfillment', compact('tasks', 'selectedTask', 'statusFilter', 'search'));
    }

    /**
     * Barcode Verification Endpoint (Supports Keyboard Wedge Scanners & AJAX)
     */
    public function verifyBarcode(Request $request, PickingTask $task): JsonResponse|RedirectResponse
    {
        $request->validate(['barcode' => 'required|string']);

        try {
            $result = $this->fulfillmentEngine->verifyBarcode($task, $request->input('barcode'), auth()->id() ?? 1);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json($result);
            }

            return redirect()->back()->with('success', $result['message']);
        } catch (\InvalidArgumentException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Short Pick / Exception Reporting with Financial Loss Rules
     */
    public function reportException(Request $request, PickingTask $task): RedirectResponse
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:picking_items,id',
            'reason' => 'required|string',
            'actual_qty' => 'required|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        $result = $this->fulfillmentEngine->reportException(
            $task,
            (int)$validated['item_id'],
            $validated['reason'],
            (int)$validated['actual_qty'],
            $validated['notes'] ?? '',
            auth()->id() ?? 1
        );

        if ($result['escalated']) {
            return redirect()->back()->with('error', "🚨 " . $result['message']);
        }

        return redirect()->back()->with('success', "✔ " . $result['message']);
    }

    /**
     * Final Warehouse Action: "Seal Package & Ready for Dispatch"
     */
    public function sealAndMarkReady(Request $request, PickingTask $task): RedirectResponse
    {
        $validated = $request->validate([
            'package_type' => 'required|string',
            'weight_kg' => 'required|numeric|min:0.1',
            'package_count' => 'nullable|integer|min:1',
            'packing_notes' => 'nullable|string',
        ]);

        try {
            $order = $this->fulfillmentEngine->sealAndMarkReadyForDispatch(
                $task,
                [
                    'package_type' => $validated['package_type'],
                    'weight_kg' => $validated['weight_kg'],
                    'package_count' => $validated['package_count'] ?? 1,
                    'packing_notes' => $validated['packing_notes'] ?? null,
                ],
                auth()->id() ?? 1
            );

            // Query next highest priority pending task in queue
            $nextTask = PickingTask::whereIn('status', ['pending', 'assigned', 'picking', 'picked', 'packed'])
                ->where('id', '!=', $task->id)
                ->orderByRaw("FIELD(priority, 'urgent', 'high', 'medium', 'low')")
                ->orderBy('created_at', 'asc')
                ->first();

            $redirectParams = $nextTask ? ['task_id' => $nextTask->id] : [];

            return redirect()->route('organize.fulfillment.index', $redirectParams)
                ->with('success', 'Package sealed successfully. Transport Department has been notified.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\OrganizeStock\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PickingTask;
use App\Services\Warehouse\PickPackService;
use App\Domain\Warehouse\WarehouseExecutionEngine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PickingController extends Controller
{
    public function __construct(
        protected PickPackService $pickPackService,
        protected WarehouseExecutionEngine $warehouseEngine
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'status', 'tab']);
        $tab = $filters['tab'] ?? 'pending_pick';

        $query = PickingTask::with(['items.product', 'items.sourceBin', 'assignedUser', 'warehouse', 'dispatchTask']);

        if ($tab === 'packing') {
            $query->whereIn('status', ['picked', 'packing']);
        } elseif ($tab === 'dispatch') {
            $query->whereIn('status', ['packed', 'queued']);
        } elseif ($tab === 'history') {
            $query->whereIn('status', ['dispatched', 'completed', 'cancelled']);
        } else {
            // Default pending pick queue
            $query->whereIn('status', ['pending', 'assigned', 'picking']);
        }

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('task_number', 'like', "%{$search}%")
                  ->orWhere('order_reference', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        $tasks = $query->orderByRaw("FIELD(priority, 'urgent', 'high', 'medium', 'low')")
                       ->orderBy('created_at', 'asc')
                       ->paginate(15)
                       ->withQueryString();

        $selectedId = $request->get('task_id', $tasks->first()?->id);
        $selectedTask = $selectedId ? PickingTask::with(['items.product', 'items.sourceBin', 'warehouse', 'assignedUser', 'dispatchTask'])->find($selectedId) : null;

        return view('organize-stock.picking', compact('tasks', 'selectedTask', 'filters', 'tab'));
    }

    public function startPicking(PickingTask $task): RedirectResponse
    {
        try {
            $this->warehouseEngine->startPicking($task, auth()->id() ?? 1);
            return redirect()->back()->with('success', "Picking started for Task #{$task->task_number}!");
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function verifyItem(int $itemId): RedirectResponse
    {
        $this->pickPackService->verifyItem($itemId);

        return redirect()->back()->with('success', 'Checklist item verified successfully!');
    }

    public function completePicking(PickingTask $task): RedirectResponse
    {
        try {
            $this->warehouseEngine->completePicking($task, auth()->id() ?? 1);

            return redirect()->route('organize.picking.index', ['tab' => 'packing'])
                             ->with('success', "Picking completed! Task #{$task->task_number} moved to Packing Queue.");
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function completePacking(PickingTask $task): RedirectResponse
    {
        try {
            $this->warehouseEngine->completePacking($task, auth()->id() ?? 1);

            return redirect()->route('organize.picking.index', ['tab' => 'dispatch'])
                             ->with('success', "Packing completed! Task #{$task->task_number} moved to Dispatch Queue.");
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function dispatchGoods(Request $request, PickingTask $task): RedirectResponse
    {
        $validated = $request->validate([
            'carrier' => 'required|string|max:100',
            'tracking_number' => 'required|string|max:100',
            'vehicle_number' => 'nullable|string|max:50',
        ]);

        try {
            $order = $this->warehouseEngine->dispatchGoods($task, $validated, auth()->id() ?? 1);

            return redirect()->route('organize.picking.index', ['tab' => 'history'])
                             ->with('success', "Goods Dispatched! Order #{$order->order_number} status updated to DISPATCHED and physical inventory decremented.");
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}

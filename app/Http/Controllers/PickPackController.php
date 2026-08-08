<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\PickingTask;
use App\Services\Warehouse\PickPackService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PickPackController extends Controller
{
    public function __construct(protected PickPackService $pickPackService) {}

    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'status']);
        $tasks = $this->pickPackService->getPickingQueue($filters, 20);

        $selectedId = $request->get('task_id', $tasks->first()?->id);
        $selectedTask = $selectedId ? PickingTask::with(['items.product', 'items.sourceBin', 'warehouse', 'assignedUser'])->find($selectedId) : null;

        return view('organize.pickpack', compact('tasks', 'selectedTask', 'filters'));
    }

    public function verifyItem(int $itemId): RedirectResponse
    {
        $this->pickPackService->verifyItem($itemId);

        return redirect()->back()->with('success', 'Checklist item verified successfully!');
    }

    public function completeTask(int $taskId): RedirectResponse
    {
        try {
            $dispatch = $this->pickPackService->completePicking($taskId);

            return redirect()->route('organize.pickpack.index')
                             ->with('success', "Picking & Packing completed! Dispatch Task #{$dispatch->dispatch_number} generated for Transport Portal.");
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}

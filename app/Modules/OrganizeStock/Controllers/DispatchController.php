<?php

declare(strict_types=1);

namespace App\Modules\OrganizeStock\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DispatchTask;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DispatchController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $query = DispatchTask::with('pickingTask');

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('dispatch_number', 'like', "%{$search}%")
                  ->orWhere('order_reference', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('shipping_label_code', 'like', "%{$search}%");
            });
        }

        $dispatches = $query->latest()->paginate(15);

        $kpis = [
            'total' => DispatchTask::count(),
            'pending_pack' => DispatchTask::where('status', 'pending_pack')->count(),
            'packed' => DispatchTask::where('status', 'packed')->count(),
            'dispatched' => DispatchTask::where('status', 'dispatched')->count(),
        ];

        return view('organize.dispatch', compact('dispatches', 'kpis', 'status', 'search'));
    }

    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending_pack,packed,dispatched,delivered,cancelled'],
            'shipping_label_code' => ['nullable', 'string', 'max:100'],
        ]);

        $dispatch = DispatchTask::findOrFail($id);
        $dispatch->update($validated);

        return redirect()->route('organize.dispatch.index')
                         ->with('success', "Dispatch Task {$dispatch->dispatch_number} updated to status '{$dispatch->status}' successfully!");
    }
}

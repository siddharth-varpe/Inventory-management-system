<?php

declare(strict_types=1);

namespace App\Services\Warehouse;

use App\Models\DispatchTask;
use App\Models\PickingItem;
use App\Models\PickingTask;
use App\Domain\Transport\TransportManagementEngine;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class PickPackService
{
    public function __construct(
        protected TransportManagementEngine $transportEngine
    ) {}

    public function getPickingQueue(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = PickingTask::with(['items.product', 'items.sourceBin', 'assignedUser', 'warehouse']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        } else {
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

        // Sort by Priority (urgent > high > medium > low) -> FIFO (created_at asc)
        return $query->orderByRaw("FIELD(priority, 'urgent', 'high', 'medium', 'low')")
                     ->orderBy('created_at', 'asc')
                     ->paginate($perPage)
                     ->withQueryString();
    }

    public function verifyItem(int $itemId): bool
    {
        $item = PickingItem::findOrFail($itemId);
        $item->update([
            'is_verified' => true,
            'picked_quantity' => $item->requested_quantity,
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        return true;
    }

    public function completePicking(int $taskId): DispatchTask
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($taskId) {
            $task = PickingTask::with('items.product')->findOrFail($taskId);

            // Entry Condition 1 & 2: Verification Check
            if (!$task->is_all_verified) {
                throw new \InvalidArgumentException("Cannot complete dispatch task until all items on the checklist are verified!");
            }

            // Deduct physical & reserved stock across verified products
            foreach ($task->items as $item) {
                if ($item->product) {
                    $product = $item->product;
                    $product->decrement('physical_stock', $item->requested_quantity);
                    if ($product->reserved_stock >= $item->requested_quantity) {
                        $product->decrement('reserved_stock', $item->requested_quantity);
                    }
                }
            }

            // Entry Condition 3 & 4: Seal Package & Set Status = "Seal & Ready to Dispatch"
            $task->update([
                'status' => 'seal_ready',
                'completed_at' => now(),
            ]);

            // Generate Dispatch Task for Trackability
            $totalItems = $task->items->sum('requested_quantity');
            $dispRef = 'DISP-' . date('Ymd') . '-' . strtoupper(Str::random(4));

            $dispatch = DispatchTask::create([
                'dispatch_number' => $dispRef,
                'picking_task_id' => $task->id,
                'order_reference' => $task->order_reference,
                'customer_name' => $task->customer_name ?? 'Customer Order',
                'total_items' => $totalItems,
                'total_weight_kg' => $totalItems * 1.5,
                'shipping_label_code' => 'LBL-' . strtoupper(Str::random(8)),
                'status' => 'queued',
                'created_by' => auth()->id(),
            ]);

            // Handover to Transport Department Phase 1 (Automated Intake)
            $this->transportEngine->createTransportTaskFromSealedPickingTask($task);

            // Dispatch Integration Event
            event(new \App\Events\Integration\PickingCompleted(
                pickingTaskId: $task->id,
                orderReference: $task->order_reference,
                dispatchReference: $dispRef,
                customerName: $task->customer_name,
                weight: (float) ($totalItems * 1.5),
                volume: 0.5
            ));

            return $dispatch;
        });
    }
}

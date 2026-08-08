<?php

declare(strict_types=1);

namespace App\Domain\Tasks;

use App\Domain\Contracts\TaskGeneratorInterface;
use App\Models\DispatchTask;
use App\Models\PickingTask;
use App\Models\StorageRequest;
use App\Models\WarehouseTransfer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TaskGenerator implements TaskGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function generatePutAwayTask(array $data): StorageRequest
    {
        Log::info("TaskGenerator: Generating Automated Put-Away Task for Product #{$data['product_id']}");

        return StorageRequest::create([
            'request_number' => 'REQ-PA-' . strtoupper(Str::random(6)),
            'product_id' => $data['product_id'],
            'stock_receipt_id' => $data['stock_receipt_id'] ?? null,
            'quantity' => $data['quantity'] ?? 1,
            'batch_number' => $data['batch_number'] ?? null,
            'supplier_name' => $data['supplier_name'] ?? 'General Supplier',
            'priority' => $data['priority'] ?? 'normal',
            'status' => 'pending',
            'created_by' => $data['user_id'] ?? (auth()->id() ?? 1),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function generatePickingTask(array $data): PickingTask
    {
        Log::info("TaskGenerator: Generating Automated Picking Task for Order #{$data['order_no']}");

        $task = PickingTask::create([
            'task_number' => 'PICK-' . strtoupper(Str::random(6)),
            'order_number' => $data['order_no'] ?? ('ORD-' . rand(1000, 9999)),
            'status' => 'pending',
            'priority' => $data['priority'] ?? 'normal',
            'total_items' => count($data['items'] ?? [1]),
            'created_by' => $data['user_id'] ?? (auth()->id() ?? 1),
        ]);

        if (!empty($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                $task->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity_requested' => $item['quantity'],
                    'quantity_picked' => 0,
                    'status' => 'pending',
                ]);
            }
        }

        return $task;
    }

    /**
     * {@inheritdoc}
     */
    public function generatePackingTask(array $data): DispatchTask
    {
        Log::info("TaskGenerator: Generating Automated Packing & Dispatch Task for Order #{$data['order_no']}");

        return DispatchTask::create([
            'task_number' => 'DSP-' . strtoupper(Str::random(6)),
            'order_number' => $data['order_no'] ?? ('ORD-' . rand(1000, 9999)),
            'picking_task_id' => $data['picking_task_id'] ?? null,
            'customer_name' => $data['customer_name'] ?? 'Enterprise Customer',
            'status' => 'pending_pack',
            'carrier' => $data['carrier'] ?? 'Express Logistics',
            'created_by' => $data['user_id'] ?? (auth()->id() ?? 1),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function generateDispatchTask(array $data): DispatchTask
    {
        return $this->generatePackingTask($data);
    }

    /**
     * {@inheritdoc}
     */
    public function generateTransferTask(array $data): WarehouseTransfer
    {
        Log::info("TaskGenerator: Generating Automated Warehouse Transfer Task for Product #{$data['product_id']}");

        return WarehouseTransfer::create([
            'transfer_number' => 'TRF-' . strtoupper(Str::random(6)),
            'product_id' => $data['product_id'],
            'from_warehouse_id' => $data['from_warehouse_id'],
            'to_warehouse_id' => $data['to_warehouse_id'],
            'quantity' => $data['quantity'],
            'status' => 'pending',
            'reason' => $data['reason'] ?? 'Automated Rebalance',
            'created_by' => $data['user_id'] ?? (auth()->id() ?? 1),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function generateCycleCountTask(array $data): StorageRequest
    {
        return $this->generatePutAwayTask($data);
    }
}

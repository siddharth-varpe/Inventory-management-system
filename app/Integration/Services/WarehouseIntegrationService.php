<?php

declare(strict_types=1);

namespace App\Integration\Services;

use App\Integration\Contracts\WarehouseIntegrationInterface;
use App\Models\DispatchTask;
use App\Models\StorageRequest;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WarehouseIntegrationService implements WarehouseIntegrationInterface
{
    public function createStorageRequest(array $payload): StorageRequest
    {
        return DB::transaction(function () use ($payload) {
            $req = StorageRequest::create([
                'request_number' => 'STR-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(2))),
                'product_id' => $payload['product_id'],
                'stock_receipt_id' => $payload['stock_receipt_id'] ?? null,
                'quantity' => $payload['quantity'],
                'batch_number' => $payload['batch_number'] ?? null,
                'supplier_name' => $payload['supplier_name'] ?? null,
                'priority' => $payload['priority'] ?? 'normal',
                'status' => 'pending',
                'created_by' => $payload['user_id'] ?? (auth()->id() ?? 1),
            ]);

            Log::info("Integration: Automated StorageRequest #{$req->request_number} generated for Product #{$req->product_id}");
            return $req;
        });
    }

    public function generateDispatchTask(array $payload): void
    {
        DB::transaction(function () use ($payload) {
            $defaultWh = Warehouse::first();
            $dispatchNumber = $payload['dispatch_reference'] ?? ('DISP-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(2))));
            
            DispatchTask::firstOrCreate(
                ['dispatch_number' => $dispatchNumber],
                [
                    'picking_task_id' => $payload['picking_task_id'] ?? null,
                    'order_reference' => $payload['order_reference'] ?? ('ORD-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(2)))),
                    'customer_name' => $payload['customer_name'] ?? 'General Customer',
                    'delivery_address' => $payload['delivery_address'] ?? 'Default Customer Address',
                    'total_items' => $payload['total_items'] ?? 1,
                    'total_weight_kg' => $payload['weight'] ?? 10.5,
                    'shipping_label_code' => 'LBL-' . strtoupper(bin2hex(random_bytes(4))),
                    'status' => 'pending_assignment',
                    'created_by' => auth()->id() ?? 1,
                ]
            );

            Log::info("Integration: Generated/Ensured Dispatch Task for Transport Portal from Picking Task #{$payload['picking_task_id']}");
        });
    }
}

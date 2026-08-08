<?php

declare(strict_types=1);

namespace App\Domain\Sales;

use App\Models\SalesOrder;
use App\Domain\Warehouse\WarehouseExecutionEngine;
use App\Domain\Transport\TransportManagementEngine;
use Illuminate\Support\Facades\Log;

class SendGoodsConnector
{
    public function __construct(
        protected WarehouseExecutionEngine $warehouseEngine,
        protected TransportManagementEngine $transportEngine
    ) {}

    /**
     * Build Dispatch Request & Execute Operation A (Warehouse Task) + Operation B (Transport Request).
     */
    public function createDispatchRequest(SalesOrder $order): array
    {
        $order->load(['customer', 'warehouse', 'items.product']);

        // Operation A: Generate Live Warehouse Pick Task (Organize Stock Portal)
        $task = $this->warehouseEngine->createTaskFromSalesOrder($order);

        // Operation B: Generate Live Transport Request (Transport Portal)
        $transportReq = $this->transportEngine->createTransportRequestFromSalesOrder($order);

        $dispatchItems = [];
        foreach ($order->items as $item) {
            if ($item->reserved_qty > 0 || $item->ordered_qty > 0) {
                $dispatchItems[] = [
                    'product_id' => $item->product_id,
                    'sku' => $item->product->sku ?? 'N/A',
                    'name' => $item->product->name ?? 'Product',
                    'reserved_qty' => $item->reserved_qty > 0 ? $item->reserved_qty : $item->ordered_qty,
                    'unit_price' => (float)$item->unit_price,
                    'line_total' => (float)$item->line_total,
                ];
            }
        }

        $payload = [
            'event' => 'SalesOrderReadyForDispatch',
            'sales_order_id' => $order->id,
            'order_number' => $order->order_number,
            'task_number' => $task->task_number,
            'transport_request_number' => $transportReq->request_number,
            'customer_id' => $order->customer_id,
            'customer_name' => $order->customer->company_name ?? 'N/A',
            'warehouse_id' => $order->warehouse_id,
            'warehouse_name' => $order->warehouse->name ?? 'Main Warehouse',
            'delivery_address' => $order->delivery_address ?? 'Primary Customer Address',
            'dispatch_priority' => $order->order_priority ?? 'normal',
            'expected_dispatch_date' => $order->expected_dispatch_date ? $order->expected_dispatch_date->format('Y-m-d') : date('Y-m-d'),
            'items' => $dispatchItems,
            'total_items_count' => count($dispatchItems),
            'generated_at' => now()->toIso8601String(),
        ];

        Log::info("SendGoodsConnector: Dual Operations Executed for Order #{$order->order_number} -> Task #{$task->task_number} & Transport Request #{$transportReq->request_number}");

        return $payload;
    }
}

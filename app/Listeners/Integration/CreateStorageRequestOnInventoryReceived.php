<?php

declare(strict_types=1);

namespace App\Listeners\Integration;

use App\Events\Integration\InventoryReceived;
use App\Integration\Contracts\WarehouseIntegrationInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class CreateStorageRequestOnInventoryReceived
{
    public function __construct(protected WarehouseIntegrationInterface $warehouseIntegration) {}

    public function handle(InventoryReceived $event): void
    {
        Log::info("Listener: Handling InventoryReceived event for Product #{$event->productId}");

        $this->warehouseIntegration->createStorageRequest([
            'product_id' => $event->productId,
            'stock_receipt_id' => $event->stockReceiptId,
            'quantity' => $event->quantity,
            'batch_number' => $event->batchNumber,
            'supplier_name' => $event->supplierName,
            'priority' => $event->priority,
            'user_id' => $event->userId,
        ]);
    }
}

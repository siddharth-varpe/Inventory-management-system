<?php

declare(strict_types=1);

namespace App\Listeners\Integration;

use App\Events\Integration\WarehouseExceptionDetected;
use App\Integration\Contracts\InventoryIntegrationInterface;
use Illuminate\Support\Facades\Log;

class ReconcileInventoryOnWarehouseException
{
    public function __construct(protected InventoryIntegrationInterface $inventoryIntegration) {}

    public function handle(WarehouseExceptionDetected $event): void
    {
        Log::info("Listener: Handling WarehouseExceptionDetected event for Product #{$event->productId}");

        $this->inventoryIntegration->reconcileInventoryLoss(
            $event->productId,
            $event->quantity,
            $event->reason,
            $event->action
        );
    }
}

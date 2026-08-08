<?php

declare(strict_types=1);

namespace App\Listeners\Integration;

use App\Events\Integration\InventoryLocationAssigned;
use App\Integration\Contracts\InventoryIntegrationInterface;
use Illuminate\Support\Facades\Log;

class UpdateProductLocationOnPutAwayCompleted
{
    public function __construct(protected InventoryIntegrationInterface $inventoryIntegration) {}

    public function handle(InventoryLocationAssigned $event): void
    {
        Log::info("Listener: Handling InventoryLocationAssigned event for Product #{$event->productId}");

        $this->inventoryIntegration->updateProductLocation(
            $event->productId,
            $event->warehouseName,
            $event->coordinate
        );
    }
}

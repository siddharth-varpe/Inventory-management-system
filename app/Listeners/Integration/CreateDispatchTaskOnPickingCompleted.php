<?php

declare(strict_types=1);

namespace App\Listeners\Integration;

use App\Events\Integration\PickingCompleted;
use App\Integration\Contracts\WarehouseIntegrationInterface;
use Illuminate\Support\Facades\Log;

class CreateDispatchTaskOnPickingCompleted
{
    public function __construct(protected WarehouseIntegrationInterface $warehouseIntegration) {}

    public function handle(PickingCompleted $event): void
    {
        Log::info("Listener: Handling PickingCompleted event for Task #{$event->pickingTaskId}");

        $this->warehouseIntegration->generateDispatchTask([
            'picking_task_id' => $event->pickingTaskId,
            'dispatch_reference' => $event->dispatchReference,
            'order_reference' => $event->orderReference,
            'customer_name' => $event->customerName,
            'weight' => $event->weight,
            'volume' => $event->volume,
        ]);
    }
}

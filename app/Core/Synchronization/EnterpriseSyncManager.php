<?php

declare(strict_types=1);

namespace App\Core\Synchronization;

use App\Core\Cache\EnterpriseCacheManager;
use App\Core\Correlation\CorrelationContext;
use App\Core\DTOs\EventPayloadDTO;
use App\Core\EventBus\EnterpriseEventBus;
use App\Core\NotificationEngine\EnterpriseNotificationEngine;
use Illuminate\Support\Facades\Log;

class EnterpriseSyncManager
{
    public function __construct(
        protected EnterpriseEventBus $eventBus,
        protected EnterpriseCacheManager $cacheManager,
        protected EnterpriseNotificationEngine $notificationEngine
    ) {}

    /**
     * Synchronize a portal business event across all enterprise systems.
     */
    public function synchronize(string $sourcePortal, string $eventName, array $data): void
    {
        $correlationId = CorrelationContext::getCorrelationId();
        $workflowId = CorrelationContext::getWorkflowId();

        $dto = new EventPayloadDTO(
            eventName: $eventName,
            sourcePortal: $sourcePortal,
            correlationId: $correlationId,
            workflowId: $workflowId,
            data: $data,
            userId: auth()->id()
        );

        Log::info("EnterpriseSyncManager: Synchronizing '{$eventName}' from '{$sourcePortal}'");

        $this->eventBus->publish($dto);
        $this->cacheManager->invalidateAllDashboards();
    }
}

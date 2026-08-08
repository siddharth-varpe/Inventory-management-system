<?php

declare(strict_types=1);

namespace App\Core\WorkflowEngine;

use App\Core\Cache\EnterpriseCacheManager;
use App\Core\Correlation\CorrelationContext;
use App\Core\DTOs\EventPayloadDTO;
use App\Core\DTOs\WorkflowStateDTO;
use App\Core\EventBus\EnterpriseEventBus;
use App\Core\NotificationEngine\EnterpriseNotificationEngine;
use App\Core\Transactions\TransactionalWorkflowRunner;
use Closure;
use Illuminate\Support\Facades\Log;

class WorkflowEngine
{
    public function __construct(
        protected EnterpriseEventBus $eventBus,
        protected EnterpriseCacheManager $cacheManager,
        protected EnterpriseNotificationEngine $notificationEngine
    ) {}

    /**
     * Execute an enterprise workflow atomically across portals with correlation tracking.
     */
    public function executeWorkflow(string $workflowName, string $sourcePortal, Closure $workflowAction, array $eventPayload = []): WorkflowStateDTO
    {
        $correlationId = CorrelationContext::getCorrelationId();
        $workflowId = CorrelationContext::getWorkflowId();

        $state = new WorkflowStateDTO($workflowId, $correlationId, $workflowName, 'in_progress');

        return TransactionalWorkflowRunner::run($workflowName, function () use ($workflowName, $sourcePortal, $workflowAction, $eventPayload, $state, $correlationId, $workflowId) {
            // 1. Execute core workflow action
            $result = $workflowAction();
            $state->addStep('execute_action', 'completed', ['result' => 'success']);

            // 2. Publish domain event to event bus
            if (!empty($eventPayload)) {
                $dto = new EventPayloadDTO(
                    eventName: $eventPayload['name'] ?? 'WorkflowCompleted',
                    sourcePortal: $sourcePortal,
                    correlationId: $correlationId,
                    workflowId: $workflowId,
                    data: $eventPayload['data'] ?? [],
                    userId: auth()->id()
                );
                $this->eventBus->publish($dto);
                $state->addStep('publish_event', 'completed', ['event_name' => $dto->eventName]);
            }

            // 3. Invalidate relevant caches
            $this->cacheManager->invalidateAllDashboards();
            $state->addStep('invalidate_cache', 'completed');

            // 4. Send role notifications
            $this->notificationEngine->notifyRole(
                role: 'all',
                title: "Workflow '{$workflowName}' Executed",
                message: "Enterprise workflow #{$workflowId} completed successfully.",
                link: '/dashboard'
            );
            $state->addStep('notify_roles', 'completed');

            $state->status = 'completed';
            Log::info("WorkflowEngine: Finished workflow '{$workflowName}' [State: completed]");
            return $state;
        });
    }
}

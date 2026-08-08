<?php

declare(strict_types=1);

namespace App\Domain\Orchestrator;

use App\Domain\Contracts\AuditCoordinatorInterface;
use App\Domain\Contracts\NotificationEngineInterface;
use App\Domain\Contracts\OrchestratorEngineInterface;
use App\Domain\Contracts\StateMachineInterface;
use App\Domain\Contracts\SyncEngineInterface;
use App\Domain\Contracts\TaskGeneratorInterface;
use App\Domain\DTO\DomainEventData;
use App\Domain\Events\GoodsReceiptCompleted;
use App\Domain\Events\PickingCompleted;
use App\Domain\Events\PutAwayCompleted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EnterpriseOrchestrationEngine implements OrchestratorEngineInterface
{
    public function __construct(
        protected TaskGeneratorInterface $taskGenerator,
        protected StateMachineInterface $stateMachine,
        protected SyncEngineInterface $syncEngine,
        protected NotificationEngineInterface $notificationEngine,
        protected AuditCoordinatorInterface $auditCoordinator
    ) {}

    /**
     * {@inheritdoc}
     */
    public function processEvent(DomainEventData $event): void
    {
        Log::info("EnterpriseOrchestrationEngine: Orchestrating event [{$event->eventType}] (Ref: {$event->referenceNo})");

        DB::transaction(function () use ($event) {
            // 1. Automatic Task Generation depending on Event Type
            if ($event->eventType === GoodsReceiptCompleted::class || str_contains($event->eventType, 'GoodsReceiptCompleted')) {
                $this->taskGenerator->generatePutAwayTask($event->payload);
            } elseif ($event->eventType === PickingCompleted::class || str_contains($event->eventType, 'PickingCompleted')) {
                $this->taskGenerator->generatePackingTask($event->payload);
            }

            // 2. Synchronize Inventory & Occupancy
            $this->syncEngine->synchronize($event);

            // 3. Dispatch Role & Channel Notifications
            $this->notificationEngine->notify($event);

            // 4. Record Immutable Audit Logs
            $this->auditCoordinator->recordAudit($event);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function executeWorkflow(string $workflowName, array $payload): bool
    {
        Log::info("EnterpriseOrchestrationEngine: Executing Workflow Pipeline [{$workflowName}]");

        return DB::transaction(function () use ($workflowName, $payload) {
            switch ($workflowName) {
                case 'ProcurementReceivingPipeline':
                case 'GoodsReceiptPipeline':
                    $this->taskGenerator->generatePutAwayTask($payload);
                    break;

                case 'ThreeWayMatchPipeline':
                    Log::info("EnterpriseOrchestrationEngine: Three-Way Match Pipeline authorized for payment processing.", $payload);
                    break;

                case 'OrderFulfillmentPipeline':
                    $pickTask = $this->taskGenerator->generatePickingTask($payload);
                    $this->taskGenerator->generatePackingTask([
                        'order_no' => $pickTask->order_number,
                        'picking_task_id' => $pickTask->id,
                    ]);
                    break;

                default:
                    Log::warning("EnterpriseOrchestrationEngine: Unknown workflow pipeline [{$workflowName}]");
                    return false;
            }
            return true;
        });
    }
}

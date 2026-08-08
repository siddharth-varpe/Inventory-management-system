<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Audit\EnterpriseAuditCoordinator;
use App\Domain\Contracts\AuditCoordinatorInterface;
use App\Domain\Contracts\EventBusInterface;
use App\Domain\Contracts\NotificationEngineInterface;
use App\Domain\Contracts\OrchestratorEngineInterface;
use App\Domain\Contracts\StateMachineInterface;
use App\Domain\Contracts\SyncEngineInterface;
use App\Domain\Contracts\TaskGeneratorInterface;
use App\Domain\EventBus\EnterpriseEventBus;
use App\Domain\Notifications\EnterpriseNotificationEngine;
use App\Domain\Orchestrator\EnterpriseOrchestrationEngine;
use App\Domain\StateMachine\WorkflowStateMachine;
use App\Domain\Synchronization\EnterpriseSyncEngine;
use App\Domain\Tasks\TaskGenerator;
use Illuminate\Support\ServiceProvider;

class EnterpriseOrchestrationServiceProvider extends ServiceProvider
{
    /**
     * Register enterprise domain orchestration services.
     */
    public function register(): void
    {
        $this->app->singleton(TaskGeneratorInterface::class, TaskGenerator::class);
        $this->app->singleton(StateMachineInterface::class, WorkflowStateMachine::class);
        $this->app->singleton(SyncEngineInterface::class, EnterpriseSyncEngine::class);
        $this->app->singleton(NotificationEngineInterface::class, EnterpriseNotificationEngine::class);
        $this->app->singleton(AuditCoordinatorInterface::class, EnterpriseAuditCoordinator::class);

        $this->app->singleton(OrchestratorEngineInterface::class, EnterpriseOrchestrationEngine::class);

        $this->app->singleton(EventBusInterface::class, function ($app) {
            $bus = new EnterpriseEventBus();
            $bus->setOrchestrator($app->make(OrchestratorEngineInterface::class));
            return $bus;
        });
    }

    /**
     * Bootstrap orchestration services.
     */
    public function boot(): void
    {
        // Enterprise Orchestration Engine ready
    }
}

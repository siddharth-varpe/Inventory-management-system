<?php

declare(strict_types=1);

namespace App\Domain\Contracts;

use App\Domain\DTO\DomainEventData;

interface OrchestratorEngineInterface
{
    /**
     * Process an incoming domain event through the Enterprise Orchestration Engine.
     */
    public function processEvent(DomainEventData $event): void;

    /**
     * Execute a named workflow pipeline.
     */
    public function executeWorkflow(string $workflowName, array $payload): bool;
}

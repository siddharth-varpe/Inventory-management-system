<?php

declare(strict_types=1);

namespace App\Domain\Contracts;

use App\Domain\DTO\DomainEventData;

interface EventBusInterface
{
    /**
     * Dispatch a domain event through the Enterprise Orchestration Engine.
     */
    public function dispatch(DomainEventData $event): void;

    /**
     * Register an event listener callback for a specific domain event type.
     */
    public function listen(string $eventType, callable|string $handler): void;
}

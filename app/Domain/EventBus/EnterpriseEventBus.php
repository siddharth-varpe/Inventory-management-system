<?php

declare(strict_types=1);

namespace App\Domain\EventBus;

use App\Domain\Contracts\EventBusInterface;
use App\Domain\Contracts\OrchestratorEngineInterface;
use App\Domain\DTO\DomainEventData;
use Illuminate\Support\Facades\Log;

class EnterpriseEventBus implements EventBusInterface
{
    /**
     * Map of event handlers by event type.
     *
     * @var array<string, array<callable|string>>
     */
    protected array $handlers = [];

    /**
     * Optional orchestrator reference.
     */
    protected ?OrchestratorEngineInterface $orchestrator = null;

    public function setOrchestrator(OrchestratorEngineInterface $orchestrator): void
    {
        $this->orchestrator = $orchestrator;
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(DomainEventData $event): void
    {
        Log::info("EnterpriseEventBus: Dispatched event [{$event->eventType}] ({$event->eventId}) from module [{$event->module}]");

        // 1. Process handlers registered for this event type
        if (isset($this->handlers[$event->eventType])) {
            foreach ($this->handlers[$event->eventType] as $handler) {
                if (is_callable($handler)) {
                    $handler($event);
                } elseif (is_string($handler) && class_exists($handler)) {
                    app($handler)->handle($event);
                }
            }
        }

        // 2. Delegate to Enterprise Orchestrator Engine if configured
        if ($this->orchestrator) {
            $this->orchestrator->processEvent($event);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function listen(string $eventType, callable|string $handler): void
    {
        $this->handlers[$eventType][] = $handler;
    }
}

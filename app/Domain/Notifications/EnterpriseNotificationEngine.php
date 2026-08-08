<?php

declare(strict_types=1);

namespace App\Domain\Notifications;

use App\Domain\Contracts\NotificationEngineInterface;
use App\Domain\DTO\DomainEventData;
use Illuminate\Support\Facades\Log;

class EnterpriseNotificationEngine implements NotificationEngineInterface
{
    /**
     * {@inheritdoc}
     */
    public function notify(DomainEventData $event): void
    {
        Log::info("EnterpriseNotificationEngine: Notification dispatched for Event [{$event->eventType}] to module [{$event->module}]");
    }
}

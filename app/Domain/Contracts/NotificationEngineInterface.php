<?php

declare(strict_types=1);

namespace App\Domain\Contracts;

use App\Domain\DTO\DomainEventData;

interface NotificationEngineInterface
{
    /**
     * Dispatch domain notifications to relevant role holders & system channels.
     */
    public function notify(DomainEventData $event): void;
}

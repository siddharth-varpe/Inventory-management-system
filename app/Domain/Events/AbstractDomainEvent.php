<?php

declare(strict_types=1);

namespace App\Domain\Events;

use App\Domain\DTO\DomainEventData;

abstract class AbstractDomainEvent
{
    public DomainEventData $eventData;

    public function __construct(
        string $module,
        array $payload = [],
        ?string $referenceNo = null,
        ?int $userId = null,
        ?int $branchId = null
    ) {
        $this->eventData = new DomainEventData(
            eventType: static::class,
            module: $module,
            payload: $payload,
            referenceNo: $referenceNo,
            userId: $userId,
            branchId: $branchId
        );
    }
}

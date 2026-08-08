<?php

declare(strict_types=1);

namespace App\Domain\Contracts;

use App\Domain\DTO\DomainEventData;

interface SyncEngineInterface
{
    /**
     * Synchronize inventory balances, warehouse occupancy, and operational metrics.
     */
    public function synchronize(DomainEventData $event): void;
}

<?php

declare(strict_types=1);

namespace App\Domain\Contracts;

use App\Domain\DTO\DomainEventData;

interface AuditCoordinatorInterface
{
    /**
     * Record immutable audit and activity log entries for a domain event.
     */
    public function recordAudit(DomainEventData $event): void;
}

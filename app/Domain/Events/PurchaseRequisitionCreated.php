<?php

declare(strict_types=1);

namespace App\Domain\Events;

class PurchaseRequisitionCreated extends AbstractDomainEvent
{
    public function getEventName(): string
    {
        return 'PurchaseRequisitionCreated';
    }
}

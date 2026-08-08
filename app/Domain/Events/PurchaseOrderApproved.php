<?php

declare(strict_types=1);

namespace App\Domain\Events;

class PurchaseOrderApproved extends AbstractDomainEvent
{
    public function getEventName(): string
    {
        return 'PurchaseOrderApproved';
    }
}

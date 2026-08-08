<?php

declare(strict_types=1);

namespace App\Domain\Events;

class InvoiceMatched extends AbstractDomainEvent
{
    public function getEventName(): string
    {
        return 'InvoiceMatched';
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\Events;

class ShipmentDispatched extends AbstractDomainEvent
{
    public function __construct(
        string $module = 'OrderSupplies',
        array $payload = [],
        ?string $referenceNo = null,
        ?int $userId = null,
        ?int $branchId = null
    ) {
        parent::__construct(
            module: $module,
            payload: $payload,
            referenceNo: $referenceNo,
            userId: $userId,
            branchId: $branchId
        );
    }
}

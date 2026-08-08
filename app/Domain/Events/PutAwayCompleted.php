<?php

declare(strict_types=1);

namespace App\Domain\Events;

class PutAwayCompleted extends AbstractDomainEvent
{
    public function __construct(
        int $requestId,
        int $productId,
        int $binId,
        int $quantity,
        ?string $referenceNo = null,
        ?int $userId = null
    ) {
        parent::__construct(
            module: 'OrganizeStock',
            payload: [
                'request_id' => $requestId,
                'product_id' => $productId,
                'bin_id' => $binId,
                'quantity' => $quantity,
            ],
            referenceNo: $referenceNo,
            userId: $userId
        );
    }
}

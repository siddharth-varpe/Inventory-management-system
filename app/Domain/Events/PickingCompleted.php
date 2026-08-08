<?php

declare(strict_types=1);

namespace App\Domain\Events;

class PickingCompleted extends AbstractDomainEvent
{
    public function __construct(
        int $taskId,
        int $productId,
        int $quantity,
        ?string $referenceNo = null,
        ?int $userId = null
    ) {
        parent::__construct(
            module: 'OrganizeStock',
            payload: [
                'task_id' => $taskId,
                'product_id' => $productId,
                'quantity' => $quantity,
            ],
            referenceNo: $referenceNo,
            userId: $userId
        );
    }
}

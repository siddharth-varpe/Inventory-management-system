<?php

declare(strict_types=1);

namespace App\Domain\Events;

class GoodsReceiptCompleted extends AbstractDomainEvent
{
    public function __construct(
        int $productId,
        int $quantity,
        float $unitCost,
        ?string $batchNumber = null,
        ?string $storageCondition = null,
        ?string $qcStatus = null,
        ?string $referenceNo = null,
        ?int $userId = null
    ) {
        parent::__construct(
            module: 'ManageStock',
            payload: [
                'product_id' => $productId,
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'batch_number' => $batchNumber,
                'storage_condition' => $storageCondition,
                'qc_status' => $qcStatus,
            ],
            referenceNo: $referenceNo,
            userId: $userId
        );
    }
}

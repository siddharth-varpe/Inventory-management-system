<?php

declare(strict_types=1);

namespace App\Events\Integration;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InventoryReceived
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $productId,
        public int $quantity,
        public ?int $stockReceiptId = null,
        public ?string $batchNumber = null,
        public ?string $supplierName = null,
        public ?string $priority = 'normal',
        public ?int $userId = null
    ) {}
}

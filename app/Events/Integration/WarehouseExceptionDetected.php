<?php

declare(strict_types=1);

namespace App\Events\Integration;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WarehouseExceptionDetected
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $exceptionId,
        public int $productId,
        public int $quantity,
        public string $reason,
        public string $action
    ) {}
}

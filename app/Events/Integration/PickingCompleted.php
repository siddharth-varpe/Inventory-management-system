<?php

declare(strict_types=1);

namespace App\Events\Integration;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PickingCompleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $pickingTaskId,
        public string $orderReference,
        public string $dispatchReference,
        public ?string $customerName = null,
        public ?float $weight = 10.0,
        public ?float $volume = 0.5
    ) {}
}

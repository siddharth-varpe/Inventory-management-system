<?php

declare(strict_types=1);

namespace App\Events\Integration;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PutAwayCompleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $storageRequestId,
        public int $productId,
        public string $coordinate
    ) {}
}

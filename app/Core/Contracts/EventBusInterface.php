<?php

declare(strict_types=1);

namespace App\Core\Contracts;

use App\Core\DTOs\EventPayloadDTO;

interface EventBusInterface
{
    /**
     * Publish an enterprise domain event DTO to the bus.
     */
    public function publish(EventPayloadDTO $event): void;
}

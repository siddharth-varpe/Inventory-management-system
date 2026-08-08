<?php

declare(strict_types=1);

namespace App\Integration\Contracts;

use App\Models\Product;
use App\Models\StorageRequest;

interface WarehouseIntegrationInterface
{
    /**
     * Create automated storage request for received inventory.
     */
    public function createStorageRequest(array $payload): StorageRequest;

    /**
     * Generate dispatch task for transport logistics.
     */
    public function generateDispatchTask(array $payload): void;
}

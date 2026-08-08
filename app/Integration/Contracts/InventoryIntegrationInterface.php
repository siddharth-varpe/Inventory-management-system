<?php

declare(strict_types=1);

namespace App\Integration\Contracts;

use App\Models\Product;

interface InventoryIntegrationInterface
{
    /**
     * Update product location in Manage Stock bounded context.
     */
    public function updateProductLocation(int|Product $product, string $warehouseName, string $coordinate): void;

    /**
     * Process automated or approved stock write-off reconciliation.
     */
    public function reconcileInventoryLoss(int|Product $product, int $quantity, string $reason, string $action): void;
}

<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Inventory;
use App\Models\ProductSerial;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface InventoryRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get expiring inventory lots by date range.
     *
     * @param string $range
     * @param int $perPage
     * @return LengthAwarePaginator<Inventory>
     */
    public function getExpiringLots(string $range = '30', int $perPage = 15): LengthAwarePaginator;

    /**
     * Get active inventory batches.
     *
     * @param int $perPage
     * @return LengthAwarePaginator<Inventory>
     */
    public function getBatches(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get product serial numbers.
     *
     * @param array<string, mixed> $filters
     * @param int $perPage
     * @return LengthAwarePaginator<ProductSerial>
     */
    public function getSerials(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Get dead stock items (stock > 0 but no receipts/adjustments recently).
     *
     * @return Collection
     */
    public function getDeadStock(): Collection;
}

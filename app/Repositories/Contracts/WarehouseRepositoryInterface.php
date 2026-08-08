<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Warehouse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface WarehouseRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get paginated list of warehouses with optional search and filters.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getWarehouses(array $filters = [], int $perPage = 15): LengthAwarePaginator;
}

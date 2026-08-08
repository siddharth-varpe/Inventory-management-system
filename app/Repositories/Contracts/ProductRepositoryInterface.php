<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Search, filter, and sort products catalog.
     *
     * @param array<string, mixed> $filters
     * @param int $perPage
     * @return LengthAwarePaginator<Product>
     */
    public function getCatalog(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Archive active product.
     *
     * @param int|string $id
     * @return bool
     */
    public function archive(int|string $id): bool;

    /**
     * Restore soft deleted or archived product.
     *
     * @param int|string $id
     * @return bool
     */
    public function restore(int|string $id): bool;

    /**
     * Duplicate existing product master record.
     *
     * @param int|string $id
     * @return Product
     */
    public function duplicate(int|string $id): Product;
}

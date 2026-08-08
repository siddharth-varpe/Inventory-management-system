<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

interface CategoryRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get root level categories with children nested.
     *
     * @return Collection<int, Category>
     */
    public function getTree(): Collection;

    /**
     * Restore soft deleted category.
     *
     * @param int|string $id
     * @return bool
     */
    public function restore(int|string $id): bool;
}

<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

interface CategoryServiceInterface extends BaseServiceInterface
{
    /**
     * Get root level category tree.
     *
     * @return Collection<int, Category>
     */
    public function getCategoryTree(): Collection;

    /**
     * Restore soft deleted category.
     *
     * @param int|string $id
     * @return bool
     */
    public function restoreCategory(int|string $id): bool;

    /**
     * Delete category with child guard check.
     *
     * @param int|string $id
     * @return bool
     */
    public function safeDelete(int|string $id): bool;
}

<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

interface BrandRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Restore soft deleted brand.
     *
     * @param int|string $id
     * @return bool
     */
    public function restore(int|string $id): bool;
}

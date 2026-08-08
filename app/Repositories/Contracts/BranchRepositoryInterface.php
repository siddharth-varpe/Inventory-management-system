<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Branch;

interface BranchRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find branch by unique code.
     *
     * @param string $code
     * @return Branch|null
     */
    public function findByCode(string $code): ?Branch;
}

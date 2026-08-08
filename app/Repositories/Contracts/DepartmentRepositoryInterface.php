<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Department;

interface DepartmentRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find department by code.
     *
     * @param string $code
     * @return Department|null
     */
    public function findByCode(string $code): ?Department;
}

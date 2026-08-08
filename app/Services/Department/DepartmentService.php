<?php

declare(strict_types=1);

namespace App\Services\Department;

use App\Repositories\Contracts\DepartmentRepositoryInterface;
use App\Services\BaseService;
use App\Services\Contracts\DepartmentServiceInterface;

class DepartmentService extends BaseService implements DepartmentServiceInterface
{
    /**
     * DepartmentService constructor.
     *
     * @param DepartmentRepositoryInterface $repository
     */
    public function __construct(DepartmentRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }
}

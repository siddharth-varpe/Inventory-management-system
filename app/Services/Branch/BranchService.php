<?php

declare(strict_types=1);

namespace App\Services\Branch;

use App\Repositories\Contracts\BranchRepositoryInterface;
use App\Services\BaseService;
use App\Services\Contracts\BranchServiceInterface;

class BranchService extends BaseService implements BranchServiceInterface
{
    /**
     * BranchService constructor.
     *
     * @param BranchRepositoryInterface $repository
     */
    public function __construct(BranchRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }
}

<?php

declare(strict_types=1);

namespace App\Services\Unit;

use App\Repositories\Contracts\UnitRepositoryInterface;
use App\Services\BaseService;
use App\Services\Contracts\UnitServiceInterface;

class UnitService extends BaseService implements UnitServiceInterface
{
    /**
     * UnitService constructor.
     *
     * @param UnitRepositoryInterface $repository
     */
    public function __construct(UnitRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }
}

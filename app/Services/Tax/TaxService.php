<?php

declare(strict_types=1);

namespace App\Services\Tax;

use App\Repositories\Contracts\TaxRepositoryInterface;
use App\Services\BaseService;
use App\Services\Contracts\TaxServiceInterface;

class TaxService extends BaseService implements TaxServiceInterface
{
    /**
     * TaxService constructor.
     *
     * @param TaxRepositoryInterface $repository
     */
    public function __construct(TaxRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }
}

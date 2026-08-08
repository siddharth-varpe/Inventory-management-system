<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Tax;
use App\Repositories\Contracts\TaxRepositoryInterface;

class TaxRepository extends EloquentBaseRepository implements TaxRepositoryInterface
{
    /**
     * TaxRepository constructor.
     *
     * @param Tax $model
     */
    public function __construct(Tax $model)
    {
        parent::__construct($model);
    }
}

<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Unit;
use App\Repositories\Contracts\UnitRepositoryInterface;

class UnitRepository extends EloquentBaseRepository implements UnitRepositoryInterface
{
    /**
     * UnitRepository constructor.
     *
     * @param Unit $model
     */
    public function __construct(Unit $model)
    {
        parent::__construct($model);
    }
}

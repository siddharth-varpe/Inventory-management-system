<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\ProductAttribute;
use App\Repositories\Contracts\ProductAttributeRepositoryInterface;

class ProductAttributeRepository extends EloquentBaseRepository implements ProductAttributeRepositoryInterface
{
    /**
     * ProductAttributeRepository constructor.
     *
     * @param ProductAttribute $model
     */
    public function __construct(ProductAttribute $model)
    {
        parent::__construct($model);
    }
}

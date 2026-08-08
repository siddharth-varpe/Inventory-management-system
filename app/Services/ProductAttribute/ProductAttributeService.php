<?php

declare(strict_types=1);

namespace App\Services\ProductAttribute;

use App\Repositories\Contracts\ProductAttributeRepositoryInterface;
use App\Services\BaseService;
use App\Services\Contracts\ProductAttributeServiceInterface;

class ProductAttributeService extends BaseService implements ProductAttributeServiceInterface
{
    /**
     * ProductAttributeService constructor.
     *
     * @param ProductAttributeRepositoryInterface $repository
     */
    public function __construct(ProductAttributeRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }
}

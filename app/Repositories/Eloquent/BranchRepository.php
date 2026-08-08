<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Branch;
use App\Repositories\Contracts\BranchRepositoryInterface;

class BranchRepository extends EloquentBaseRepository implements BranchRepositoryInterface
{
    /**
     * BranchRepository constructor.
     *
     * @param Branch $model
     */
    public function __construct(Branch $model)
    {
        parent::__construct($model);
    }

    /**
     * {@inheritdoc}
     */
    public function findByCode(string $code): ?Branch
    {
        /** @var Branch|null */
        return $this->findBy('code', $code);
    }
}

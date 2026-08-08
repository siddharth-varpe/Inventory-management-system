<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Brand;
use App\Repositories\Contracts\BrandRepositoryInterface;

class BrandRepository extends EloquentBaseRepository implements BrandRepositoryInterface
{
    /**
     * BrandRepository constructor.
     *
     * @param Brand $model
     */
    public function __construct(Brand $model)
    {
        parent::__construct($model);
    }

    /**
     * {@inheritdoc}
     */
    public function restore(int|string $id): bool
    {
        /** @var Brand|null $record */
        $record = $this->model->onlyTrashed()->find($id);
        if ($record) {
            return (bool) $record->restore();
        }

        return false;
    }
}

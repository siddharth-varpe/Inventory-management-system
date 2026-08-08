<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class CategoryRepository extends EloquentBaseRepository implements CategoryRepositoryInterface
{
    /**
     * CategoryRepository constructor.
     *
     * @param Category $model
     */
    public function __construct(Category $model)
    {
        parent::__construct($model);
    }

    /**
     * {@inheritdoc}
     */
    public function getTree(): Collection
    {
        return $this->model->whereNull('parent_id')
            ->with('children')
            ->orderBy('display_order')
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function restore(int|string $id): bool
    {
        /** @var Category|null $record */
        $record = $this->model->onlyTrashed()->find($id);
        if ($record) {
            return (bool) $record->restore();
        }

        return false;
    }
}

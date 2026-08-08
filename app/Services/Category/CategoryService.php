<?php

declare(strict_types=1);

namespace App\Services\Category;

use App\Exceptions\DomainException;
use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Services\BaseService;
use App\Services\Contracts\CategoryServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class CategoryService extends BaseService implements CategoryServiceInterface
{

    /**
     * CategoryService constructor.
     *
     * @param CategoryRepositoryInterface $repository
     */
    public function __construct(CategoryRepositoryInterface $repository)
    {
        parent::__construct($repository);
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data): Category
    {
        if (empty($data['code'])) {
            $data['code'] = 'CAT-'.strtoupper(Str::random(6));
        }

        $data['created_by'] = auth()->id();

        /** @var Category */
        return parent::create($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoryTree(): Collection
    {
        /** @var CategoryRepositoryInterface $repo */
        $repo = $this->repository;
        return $repo->getTree();
    }

    /**
     * {@inheritdoc}
     */
    public function restoreCategory(int|string $id): bool
    {
        /** @var CategoryRepositoryInterface $repo */
        $repo = $this->repository;
        return $repo->restore($id);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDelete(int|string $id): bool
    {
        /** @var Category $category */
        $category = $this->getById($id, ['*'], ['children']);

        if ($category->children->isNotEmpty()) {
            throw new DomainException("Cannot delete category [{$category->name}] because it contains child sub-categories.");
        }

        return $this->delete($id);
    }
}

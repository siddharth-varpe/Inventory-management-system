<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class ProductRepository extends EloquentBaseRepository implements ProductRepositoryInterface
{
    /**
     * ProductRepository constructor.
     *
     * @param Product $model
     */
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    /**
     * {@inheritdoc}
     */
    public function getCatalog(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['category', 'brand', 'unit', 'tax']);

        // Search Filter
        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        // Classification Filters
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['brand_id'])) {
            $query->where('brand_id', $filters['brand_id']);
        }

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'trashed') {
                $query->onlyTrashed();
            } else {
                $query->where('status', $filters['status']);
            }
        }

        if (!empty($filters['warehouse_location'])) {
            $query->where('warehouse_location', $filters['warehouse_location']);
        }

        // Stock Status Filters
        if (!empty($filters['stock_status'])) {
            switch ($filters['stock_status']) {
                case 'low':
                    $query->whereColumn('physical_stock', '<=', 'reorder_level')
                          ->where('physical_stock', '>', 0);
                    break;
                case 'out_of_stock':
                    $query->where('physical_stock', '<=', 0);
                    break;
                case 'in_stock':
                    $query->where('physical_stock', '>', 0);
                    break;
            }
        }

        // Sorting
        $sortColumn = $filters['sort_by'] ?? 'id';
        $sortDirection = strtolower($filters['sort_dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        $allowedSorts = ['id', 'name', 'sku', 'code', 'physical_stock', 'cost_price', 'selling_price', 'created_at'];
        if (in_array($sortColumn, $allowedSorts, true)) {
            $query->orderBy($sortColumn, $sortDirection);
        } else {
            $query->orderByDesc('id');
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * {@inheritdoc}
     */
    public function archive(int|string $id): bool
    {
        /** @var Product $product */
        $product = $this->findOrFail($id);
        return $product->update(['status' => 'archived']);
    }

    /**
     * {@inheritdoc}
     */
    public function restore(int|string $id): bool
    {
        /** @var Product|null $record */
        $record = $this->model->onlyTrashed()->find($id);
        if ($record) {
            return (bool) $record->restore();
        }

        $activeRecord = $this->model->find($id);
        if ($activeRecord && $activeRecord->status === 'archived') {
            return $activeRecord->update(['status' => 'active']);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function duplicate(int|string $id): Product
    {
        /** @var Product $original */
        $original = $this->findOrFail($id);

        $newProduct = $original->replicate(['code', 'sku', 'barcode', 'qr_code', 'physical_stock', 'reserved_stock', 'available_stock']);
        $newProduct->name = $original->name . ' (Copy)';
        $newProduct->code = 'PRD-' . strtoupper(Str::random(6));
        $newProduct->sku = 'SKU-' . strtoupper(Str::random(8));
        $newProduct->barcode = '890' . rand(100000000, 999999999);
        $newProduct->physical_stock = 0;
        $newProduct->reserved_stock = 0;
        $newProduct->available_stock = 0;
        $newProduct->created_by = auth()->id();
        $newProduct->save();

        // Duplicate attributes
        foreach ($original->attributeValues as $attr) {
            $newProduct->attributeValues()->create([
                'product_attribute_id' => $attr->product_attribute_id,
                'attribute_value' => $attr->attribute_value,
            ]);
        }

        return $newProduct;
    }
}

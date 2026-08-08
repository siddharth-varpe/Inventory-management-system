<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductSerial;
use App\Repositories\Contracts\InventoryRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class InventoryRepository extends EloquentBaseRepository implements InventoryRepositoryInterface
{
    /**
     * InventoryRepository constructor.
     *
     * @param Inventory $model
     */
    public function __construct(Inventory $model)
    {
        parent::__construct($model);
    }

    /**
     * {@inheritdoc}
     */
    public function getExpiringLots(string $range = '30', int $perPage = 15): LengthAwarePaginator
    {
        $today = Carbon::today();
        $query = $this->model->with('product')->whereNotNull('expiry_date')->orderBy('expiry_date');

        switch ($range) {
            case 'expired':
                $query->where('expiry_date', '<', $today);
                break;
            case '7':
                $query->whereBetween('expiry_date', [$today, $today->copy()->addDays(7)]);
                break;
            case '30':
                $query->whereBetween('expiry_date', [$today, $today->copy()->addDays(30)]);
                break;
            case '90':
                $query->whereBetween('expiry_date', [$today, $today->copy()->addDays(90)]);
                break;
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * {@inheritdoc}
     */
    public function getBatches(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->with('product')
            ->whereNotNull('batch_number')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    /**
     * {@inheritdoc}
     */
    public function getSerials(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = ProductSerial::with(['product', 'inventory'])->orderByDesc('id');

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where('serial_number', 'like', "%{$search}%");
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * {@inheritdoc}
     */
    public function getDeadStock(): Collection
    {
        return Product::where('physical_stock', '>', 0)
            ->whereDoesntHave('receipts', function ($q) {
                $q->where('created_at', '>=', Carbon::now()->subDays(60));
            })
            ->whereDoesntHave('adjustments', function ($q) {
                $q->where('created_at', '>=', Carbon::now()->subDays(60));
            })
            ->with(['category', 'brand'])
            ->get();
    }
}

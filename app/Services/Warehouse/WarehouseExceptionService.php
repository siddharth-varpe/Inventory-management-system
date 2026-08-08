<?php

declare(strict_types=1);

namespace App\Services\Warehouse;

use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\WarehouseException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class WarehouseExceptionService
{
    public function getExceptions(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = WarehouseException::with(['product', 'warehouse', 'reportedBy']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['exception_type'])) {
            $query->where('exception_type', $filters['exception_type']);
        }

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('exception_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return $query->orderByDesc('id')->paginate($perPage)->withQueryString();
    }

    public function reportException(array $data): WarehouseException
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($data) {
            $product = Product::findOrFail($data['product_id']);
            $costPrice = (float)($product->cost_price ?? 0);
            $qty = abs((int)($data['affected_quantity'] ?? 1));

            $exception = WarehouseException::create([
                'exception_number' => 'EXC-' . date('Ymd') . '-' . strtoupper(Str::random(4)),
                'exception_type' => $data['exception_type'], // short_pick, missing_item, damaged_item, wrong_location, quality_failure
                'product_id' => $product->id,
                'warehouse_id' => $data['warehouse_id'] ?? null,
                'bin_id' => $data['bin_id'] ?? null,
                'picking_task_id' => $data['picking_task_id'] ?? null,
                'affected_quantity' => $qty,
                'description' => $data['description'] ?? 'Warehouse operational anomaly reported.',
                'action_taken' => $data['action_taken'] ?? 'report_exception',
                'status' => 'open',
                'reported_by' => auth()->id(),
            ]);

            // Dispatch Enterprise Integration Event
            event(new \App\Events\Integration\WarehouseExceptionDetected(
                exceptionId: $exception->id,
                productId: $product->id,
                quantity: $qty,
                reason: $data['description'] ?? 'Warehouse anomaly',
                action: $data['action_taken'] ?? 'report_exception'
            ));

            return $exception;
        });
    }
}

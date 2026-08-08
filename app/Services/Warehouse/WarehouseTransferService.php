<?php

declare(strict_types=1);

namespace App\Services\Warehouse;

use App\Models\Product;
use App\Models\WarehouseBin;
use App\Models\WarehouseTransfer;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class WarehouseTransferService
{
    public function getTransfers(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = WarehouseTransfer::with(['product', 'fromWarehouse', 'toWarehouse', 'operator']);

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('transfer_number', 'like', "%{$search}%")
                  ->orWhere('reason', 'like', "%{$search}%")
                  ->orWhereHas('product', fn($pq) => $pq->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%"));
            });
        }

        return $query->orderByDesc('id')->paginate($perPage)->withQueryString();
    }

    public function createTransfer(array $data): WarehouseTransfer
    {
        $product = Product::findOrFail($data['product_id']);

        $fromCoordinate = $data['from_coordinate'] ?? 'Main Storage';
        $toCoordinate = $data['to_coordinate'] ?? 'Picking Zone / Rack P-01';

        // Update product current rack location if moving within same warehouse or to new bin
        if (!empty($data['to_coordinate'])) {
            $product->update([
                'rack_location' => $data['to_coordinate'],
            ]);
        }

        return WarehouseTransfer::create([
            'transfer_number' => 'TRF-' . date('Ymd') . '-' . strtoupper(Str::random(4)),
            'product_id' => $product->id,
            'quantity' => (int)$data['quantity'],
            'from_warehouse_id' => $data['from_warehouse_id'] ?? null,
            'from_bin_id' => $data['from_bin_id'] ?? null,
            'from_coordinate' => $fromCoordinate,
            'to_warehouse_id' => $data['to_warehouse_id'] ?? null,
            'to_bin_id' => $data['to_bin_id'] ?? null,
            'to_coordinate' => $toCoordinate,
            'reason' => $data['reason'] ?? 'Internal Warehouse Relocation',
            'status' => 'completed',
            'operator_id' => auth()->id(),
            'approved_by' => auth()->id(),
        ]);
    }
}

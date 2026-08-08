<?php

declare(strict_types=1);

namespace App\Services\Warehouse;

use App\Models\Product;
use App\Models\StorageRequest;
use App\Models\WarehouseBin;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class PutAwayService
{
    public function getPendingRequests(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = StorageRequest::with(['product', 'stockReceipt', 'warehouse', 'preferredZone', 'assignedBin']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        } else {
            $query->whereIn('status', ['pending', 'assigned']);
        }

        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('request_number', 'like', "%{$search}%")
                  ->orWhere('batch_number', 'like', "%{$search}%")
                  ->orWhereHas('product', fn($pq) => $pq->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%"));
            });
        }

        return $query->orderByDesc('id')->paginate($perPage)->withQueryString();
    }

    public function generateStorageRequest(int $productId, int $quantity, ?string $batchNumber = null, ?int $stockReceiptId = null): StorageRequest
    {
        return StorageRequest::create([
            'request_number' => 'STR-' . date('Ymd') . '-' . strtoupper(Str::random(4)),
            'product_id' => $productId,
            'stock_receipt_id' => $stockReceiptId,
            'quantity' => $quantity,
            'batch_number' => $batchNumber,
            'priority' => 'medium',
            'status' => 'pending',
        ]);
    }

    public function confirmPutAway(int $requestId, array $locationData): bool
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($requestId, $locationData) {
            $request = StorageRequest::with('product')->findOrFail($requestId);

            $bin = WarehouseBin::with('rack.aisle.zone.warehouse')->find($locationData['bin_id'] ?? null);

            if ($bin) {
                $warehouseName = $bin->rack->aisle->zone->warehouse->name;
                $coordinateString = "{$warehouseName} / {$bin->rack->aisle->name} / {$bin->rack->name} / Shelf {$bin->shelf_number} / Bin {$bin->bin_number}";
                $bin->increment('current_occupied_qty', $request->quantity);
            } else {
                $warehouseName = $locationData['warehouse_name'] ?? 'Main Depot';
                $rackName = $locationData['rack_name'] ?? 'Rack A-01';
                $shelf = $locationData['shelf'] ?? 'Shelf 01';
                $binNum = $locationData['bin'] ?? 'Bin 01';
                $coordinateString = "{$warehouseName} / {$rackName} / {$shelf} / {$binNum}";
            }

            $request->update([
                'assigned_bin_id' => $bin->id ?? null,
                'assigned_coordinate' => $coordinateString,
                'status' => 'completed',
                'completed_by' => auth()->id(),
                'completed_at' => now(),
            ]);

            // Dispatch Enterprise Integration Event
            event(new \App\Events\Integration\InventoryLocationAssigned(
                productId: $request->product_id,
                warehouseName: $warehouseName,
                coordinate: $coordinateString,
                storageRequestId: $request->id
            ));

            event(new \App\Events\Integration\PutAwayCompleted(
                storageRequestId: $request->id,
                productId: $request->product_id,
                coordinate: $coordinateString
            ));

            return true;
        });
    }
}

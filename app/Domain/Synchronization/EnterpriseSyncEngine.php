<?php

declare(strict_types=1);

namespace App\Domain\Synchronization;

use App\Domain\Contracts\SyncEngineInterface;
use App\Domain\DTO\DomainEventData;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\WarehouseLocation;
use Illuminate\Support\Facades\Log;

class EnterpriseSyncEngine implements SyncEngineInterface
{
    /**
     * {@inheritdoc}
     */
    public function synchronize(DomainEventData $event): void
    {
        Log::info("EnterpriseSyncEngine: Re-synchronizing SSOT State for Event [{$event->eventType}] (Ref: {$event->referenceNo})");

        $payload = $event->payload;

        // 1. Recalculate Master Product Physical & Available Stock from Inventory Ledger
        if (isset($payload['product_id'])) {
            $product = Product::find($payload['product_id']);
            if ($product) {
                $physicalStock = (int) Inventory::where('product_id', $product->id)->sum('quantity');
                $reservedStock = (int) $product->reserved_stock;
                $availableStock = max(0, $physicalStock - $reservedStock);
                $status = ($availableStock > 0) ? 'active' : (($physicalStock > 0) ? 'active' : 'out_of_stock');

                $product->update([
                    'physical_stock' => $physicalStock,
                    'available_stock' => $availableStock,
                    'status' => $status,
                ]);

                Log::info("EnterpriseSyncEngine: Updated Master Product [{$product->sku}] SSOT Stock -> Physical: {$physicalStock}, Available: {$availableStock}");
            }
        }

        // 2. Recalculate Warehouse Location Occupancy
        if (isset($payload['location_id'])) {
            $location = WarehouseLocation::find($payload['location_id']);
            if ($location) {
                $occupiedQty = (int) Inventory::where('warehouse_location_id', $location->id)->sum('quantity');
                $maxCapacity = (int) ($location->capacity ?? 100);
                
                $status = ($occupiedQty >= $maxCapacity) ? 'full' : (($occupiedQty > 0) ? 'occupied' : 'available');
                $location->update([
                    'current_occupancy' => $occupiedQty,
                    'status' => $status,
                ]);

                Log::info("EnterpriseSyncEngine: Updated Warehouse Location [{$location->code}] SSOT Occupancy -> Occupied: {$occupiedQty}/{$maxCapacity} ({$status})");
            }
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Integration\Services;

use App\Integration\Contracts\InventoryIntegrationInterface;
use App\Models\Product;
use App\Models\StockAdjustment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventoryIntegrationService implements InventoryIntegrationInterface
{
    public function updateProductLocation(int|Product $product, string $warehouseName, string $coordinate): void
    {
        DB::transaction(function () use ($product, $warehouseName, $coordinate) {
            $productModel = $product instanceof Product ? $product : Product::findOrFail($product);
            
            $productModel->update([
                'warehouse_location' => $warehouseName,
                'rack_location' => $coordinate,
                'location_status' => 'assigned',
            ]);

            Log::info("Integration: Updated Product #{$productModel->id} location -> {$coordinate}");
        });
    }

    public function reconcileInventoryLoss(int|Product $product, int $quantity, string $reason, string $action): void
    {
        DB::transaction(function () use ($product, $quantity, $reason, $action) {
            $productModel = $product instanceof Product ? $product : Product::findOrFail($product);

            if ($action === 'create_writeoff' || $action === 'auto_writeoff') {
                $productModel->physical_stock = max(0, (int)$productModel->physical_stock - (int)$quantity);
                $productModel->save();

                StockAdjustment::create([
                    'reference_no' => 'ADJ-EXC-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(2))),
                    'product_id' => $productModel->id,
                    'type' => 'write_off',
                    'quantity' => -1 * abs($quantity),
                    'unit_cost' => $productModel->cost_price ?? 0.00,
                    'total_amount' => abs($quantity) * ($productModel->cost_price ?? 0.00),
                    'reason' => 'Warehouse Exception Write-off: ' . $reason,
                    'status' => 'approved',
                    'created_by' => auth()->id() ?? 1,
                    'approved_by' => auth()->id() ?? 1,
                    'approved_at' => now(),
                ]);

                Log::info("Integration: Executed Write-off for Product #{$productModel->id}, Qty: {$quantity}");
            }
        });
    }
}

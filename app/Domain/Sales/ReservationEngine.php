<?php

declare(strict_types=1);

namespace App\Domain\Sales;

use App\Models\SalesOrder;
use App\Models\Product;
use App\Models\InventoryReservation;
use App\Models\Backorder;
use Illuminate\Support\Facades\DB;

class ReservationEngine
{
    /**
     * Centralized Inventory Reservation Engine.
     * Allocates reserved_stock in Product SSOT without deducting physical stock.
     */
    public function reserveInventory(SalesOrder $order): SalesOrder
    {
        return DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $product = Product::lockForUpdate()->find($item->product_id);
                if (!$product) continue;

                $requestedQty = $item->ordered_qty;
                $physicalStock = (int)$product->physical_stock;
                $reservedStock = (int)$product->reserved_stock;

                // Live Available Stock = Physical - Reserved
                $availableStock = max(0, $physicalStock - $reservedStock);

                if ($availableStock >= $requestedQty) {
                    $allocQty = $requestedQty;
                    $backorderQty = 0;
                } else {
                    $allocQty = $availableStock;
                    $backorderQty = $requestedQty - $availableStock;
                }

                if ($allocQty > 0) {
                    // Update Product SSOT
                    $product->increment('reserved_stock', $allocQty);

                    // Create InventoryReservation Audit Record
                    $order->reservations()->create([
                        'product_id' => $product->id,
                        'reserved_qty' => $allocQty,
                        'status' => 'active',
                        'reserved_at' => now(),
                    ]);
                }

                if ($backorderQty > 0) {
                    // Create Backorder record
                    $order->backorders()->create([
                        'product_id' => $product->id,
                        'requested_qty' => $requestedQty,
                        'backordered_qty' => $backorderQty,
                        'status' => 'pending',
                    ]);
                }

                // Update Line Item reserved/backorder breakdown
                $item->update([
                    'reserved_qty' => $allocQty,
                    'backorder_qty' => $backorderQty,
                ]);
            }

            $order->update([
                'status' => 'reserved',
                'reserved_at' => now(),
            ]);

            return $order;
        });
    }

    /**
     * Release active inventory reservations for an order (e.g. on Cancellation / Rejection).
     */
    public function releaseReservation(SalesOrder $order): SalesOrder
    {
        return DB::transaction(function () use ($order) {
            $reservations = $order->reservations()->where('status', 'active')->get();

            foreach ($reservations as $res) {
                $product = Product::lockForUpdate()->find($res->product_id);
                if ($product && $res->reserved_qty > 0) {
                    $product->decrement('reserved_stock', min($product->reserved_stock, $res->reserved_qty));
                }

                $res->update([
                    'status' => 'released',
                    'released_at' => now(),
                ]);
            }

            // Update item reserved breakdown
            foreach ($order->items as $item) {
                $item->update(['reserved_qty' => 0]);
            }

            return $order;
        });
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryReservation extends Model
{
    protected $fillable = [
        'sales_order_id',
        'product_id',
        'reserved_qty',
        'status',
        'reserved_at',
        'released_at',
    ];

    protected $casts = [
        'reserved_qty' => 'integer',
        'reserved_at' => 'datetime',
        'released_at' => 'datetime',
    ];

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

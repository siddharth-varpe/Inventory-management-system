<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Backorder extends Model
{
    protected $fillable = [
        'sales_order_id',
        'product_id',
        'requested_qty',
        'backordered_qty',
        'status',
    ];

    protected $casts = [
        'requested_qty' => 'integer',
        'backordered_qty' => 'integer',
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

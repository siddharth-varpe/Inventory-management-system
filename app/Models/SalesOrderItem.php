<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesOrderItem extends Model
{
    protected $fillable = [
        'sales_order_id',
        'product_id',
        'ordered_qty',
        'reserved_qty',
        'dispatched_qty',
        'backorder_qty',
        'unit_price',
        'discount_amount',
        'taxable_value',
        'gst_rate',
        'gst_amount',
        'line_total',
    ];

    protected $casts = [
        'ordered_qty' => 'integer',
        'reserved_qty' => 'integer',
        'dispatched_qty' => 'integer',
        'backorder_qty' => 'integer',
        'unit_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'taxable_value' => 'decimal:2',
        'gst_rate' => 'decimal:2',
        'gst_amount' => 'decimal:2',
        'line_total' => 'decimal:2',
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

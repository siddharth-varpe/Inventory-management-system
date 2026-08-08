<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_number',
        'quotation_id',
        'customer_id',
        'salesperson_id',
        'warehouse_id',
        'order_date',
        'expected_dispatch_date',
        'order_priority',
        'status',
        'subtotal',
        'order_discount_amount',
        'taxable_amount',
        'cgst_amount',
        'sgst_amount',
        'igst_amount',
        'grand_total',
        'delivery_address',
        'payment_terms',
        'internal_notes',
        'customer_notes',
        'approved_by',
        'approved_at',
        'reserved_at',
        'created_by',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_dispatch_date' => 'date',
        'approved_at' => 'datetime',
        'reserved_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'order_discount_amount' => 'decimal:2',
        'taxable_amount' => 'decimal:2',
        'cgst_amount' => 'decimal:2',
        'sgst_amount' => 'decimal:2',
        'igst_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesperson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salesperson_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(InventoryReservation::class);
    }

    public function backorders(): HasMany
    {
        return $this->hasMany(Backorder::class);
    }
}

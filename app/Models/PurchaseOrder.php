<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasActivityLog;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes, HasAuditLog, HasActivityLog;

    protected $fillable = [
        'po_number',
        'supplier_id',
        'purchase_requisition_id',
        'total_amount',
        'tax_amount',
        'status',
        'shipment_status',
        'dispatch_date',
        'expected_delivery_date',
        'actual_arrival_date',
        'carrier_name',
        'tracking_reference',
        'vehicle_number',
        'received_at',
        'received_by',
        'payment_terms',
        'delivery_expected_date',
        'created_by',
        'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'delivery_expected_date' => 'date',
            'dispatch_date' => 'datetime',
            'expected_delivery_date' => 'datetime',
            'actual_arrival_date' => 'datetime',
            'received_at' => 'datetime',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasActivityLog;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StorageRequest extends Model
{
    use HasFactory, HasAuditLog, HasActivityLog;

    protected $fillable = [
        'request_number',
        'product_id',
        'stock_receipt_id',
        'quantity',
        'batch_number',
        'warehouse_id',
        'preferred_zone_id',
        'assigned_bin_id',
        'assigned_coordinate',
        'priority',
        'status',
        'assigned_user_id',
        'completed_by',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function stockReceipt(): BelongsTo
    {
        return $this->belongsTo(StockReceipt::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function preferredZone(): BelongsTo
    {
        return $this->belongsTo(WarehouseZone::class, 'preferred_zone_id');
    }

    public function assignedBin(): BelongsTo
    {
        return $this->belongsTo(WarehouseBin::class, 'assigned_bin_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }
}

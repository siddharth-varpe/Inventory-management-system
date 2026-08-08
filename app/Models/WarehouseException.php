<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasActivityLog;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarehouseException extends Model
{
    use HasFactory, HasAuditLog, HasActivityLog;

    protected $fillable = [
        'exception_number',
        'exception_type',
        'product_id',
        'warehouse_id',
        'bin_id',
        'picking_task_id',
        'affected_quantity',
        'description',
        'action_taken',
        'status',
        'reported_by',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
}

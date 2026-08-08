<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PickingItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'picking_task_id',
        'product_id',
        'source_bin_id',
        'location_coordinate',
        'requested_quantity',
        'picked_quantity',
        'is_verified',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(PickingTask::class, 'picking_task_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function sourceBin(): BelongsTo
    {
        return $this->belongsTo(WarehouseBin::class, 'source_bin_id');
    }
}

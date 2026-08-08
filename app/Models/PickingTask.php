<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasActivityLog;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PickingTask extends Model
{
    use HasFactory, HasAuditLog, HasActivityLog;

    protected $fillable = [
        'task_number',
        'order_reference',
        'customer_name',
        'picking_type',
        'priority',
        'is_fragile',
        'is_cold_chain',
        'warehouse_id',
        'assigned_user_id',
        'status',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'is_fragile' => 'boolean',
        'is_cold_chain' => 'boolean',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(PickingItem::class);
    }

    public function dispatchTask(): HasOne
    {
        return $this->hasOne(DispatchTask::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function getIsAllVerifiedAttribute(): bool
    {
        return $this->verified_items_count === $this->total_items_count && $this->total_items_count > 0;
    }

    public function getVerifiedItemsCountAttribute(): int
    {
        return $this->items->filter(function ($item) {
            return (bool)$item->is_verified || ((int)$item->picked_quantity > 0 && (int)$item->picked_quantity >= (int)$item->requested_quantity);
        })->count();
    }

    public function getTotalItemsCountAttribute(): int
    {
        return $this->items->count();
    }

    public function getCompletionPercentageAttribute(): int
    {
        $total = $this->total_items_count;
        if ($total === 0) {
            return 0;
        }

        return (int)round(($this->verified_items_count / $total) * 100);
    }

    public function getProgressColorClassAttribute(): string
    {
        $pct = $this->completion_percentage;
        if ($pct === 0) {
            return 'bg-secondary';
        }
        if ($pct < 25) {
            return 'bg-primary';
        }
        if ($pct < 75) {
            return 'bg-warning text-dark';
        }
        if ($pct < 100) {
            return 'bg-info text-white';
        }

        return 'bg-success';
    }
}

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

class Warehouse extends Model
{
    use HasFactory, SoftDeletes, HasAuditLog, HasActivityLog;

    protected $fillable = [
        'name',
        'code',
        'type',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'total_capacity',
        'occupied_capacity',
        'capacity_unit',
        'manager_id',
        'contact_phone',
        'contact_email',
        'status',
        'created_by',
        'updated_by',
    ];

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function zones(): HasMany
    {
        return $this->hasMany(WarehouseZone::class);
    }

    public function getOccupancyPercentageAttribute(): float
    {
        if ($this->total_capacity <= 0) {
            return 0.0;
        }

        return round(($this->occupied_capacity / $this->total_capacity) * 100, 1);
    }
}

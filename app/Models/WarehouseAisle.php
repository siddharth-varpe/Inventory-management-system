<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WarehouseAisle extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_zone_id',
        'name',
        'code',
        'description',
    ];

    public function zone(): BelongsTo
    {
        return $this->belongsTo(WarehouseZone::class, 'warehouse_zone_id');
    }

    public function racks(): HasMany
    {
        return $this->hasMany(WarehouseRack::class);
    }
}

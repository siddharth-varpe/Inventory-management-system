<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarehouseBin extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_rack_id',
        'shelf_number',
        'bin_number',
        'location_code',
        'barcode',
        'max_weight',
        'max_volume',
        'current_occupied_qty',
        'status',
    ];

    public function rack(): BelongsTo
    {
        return $this->belongsTo(WarehouseRack::class, 'warehouse_rack_id');
    }
}

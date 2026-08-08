<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventory extends Model
{
    use HasFactory;

    /**
     * Mass assignable attributes.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'batch_number',
        'lot_number',
        'quantity',
        'reserved_qty',
        'unit_cost',
        'selling_price',
        'mfg_date',
        'expiry_date',
        'storage_condition',
        'status',
    ];

    /**
     * Attribute casts.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'unit_cost' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'quantity' => 'integer',
            'reserved_qty' => 'integer',
            'mfg_date' => 'date',
            'expiry_date' => 'date',
        ];
    }

    /**
     * Product relationship.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

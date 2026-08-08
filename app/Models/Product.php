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

class Product extends Model
{
    use HasFactory, SoftDeletes, HasAuditLog, HasActivityLog;

    /**
     * Mass assignable attributes.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'sku',
        'barcode',
        'qr_code',
        'category_id',
        'brand_id',
        'unit_id',
        'tax_id',
        'product_type',
        'status',
        'description',
        'internal_notes',
        'image',
        'documents',
        'purchase_price',
        'cost_price',
        'selling_price',
        'mrp',
        'dealer_price',
        'wholesale_price',
        'min_selling_price',
        'track_inventory',
        'batch_tracking',
        'serial_tracking',
        'expiry_tracking',
        'min_stock',
        'max_stock',
        'reorder_level',
        'warehouse_location',
        'rack_location',
        'storage_condition',
        'primary_supplier',
        'moq',
        'physical_stock',
        'reserved_stock',
        'available_stock',
        'created_by',
        'updated_by',
    ];

    /**
     * Attribute casts.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'mrp' => 'decimal:2',
            'dealer_price' => 'decimal:2',
            'wholesale_price' => 'decimal:2',
            'min_selling_price' => 'decimal:2',
            'track_inventory' => 'boolean',
            'batch_tracking' => 'boolean',
            'serial_tracking' => 'boolean',
            'expiry_tracking' => 'boolean',
            'min_stock' => 'integer',
            'max_stock' => 'integer',
            'reorder_level' => 'integer',
            'moq' => 'integer',
            'physical_stock' => 'integer',
            'reserved_stock' => 'integer',
            'available_stock' => 'integer',
            'documents' => 'array',
        ];
    }

    /**
     * Category relationship.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Brand relationship.
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Unit relationship.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Tax relationship.
     */
    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class);
    }

    /**
     * Inventories / Lots relationship.
     */
    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    /**
     * Serial Numbers relationship.
     */
    public function serials(): HasMany
    {
        return $this->hasMany(ProductSerial::class);
    }

    /**
     * Stock Receipts relationship.
     */
    public function receipts(): HasMany
    {
        return $this->hasMany(StockReceipt::class);
    }

    /**
     * Stock Adjustments relationship.
     */
    public function adjustments(): HasMany
    {
        return $this->hasMany(StockAdjustment::class);
    }

    /**
     * Product Attribute Values relationship.
     */
    public function attributeValues(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class);
    }

    /**
     * Dynamic Stock Status Badge Text
     */
    public function getStockStatusAttribute(): string
    {
        if ($this->physical_stock <= 0) {
            return 'out_of_stock';
        }
        if ($this->physical_stock <= $this->min_stock) {
            return 'critical';
        }
        if ($this->physical_stock <= $this->reorder_level) {
            return 'low';
        }
        return 'normal';
    }

    /**
     * Profit margin percentage helper.
     */
    public function getProfitMarginAttribute(): float
    {
        if ($this->cost_price <= 0) {
            return 0.0;
        }

        return round((($this->selling_price - $this->cost_price) / $this->cost_price) * 100, 2);
    }

    /**
     * Get full public URL for product image.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }

        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
            return $this->image;
        }

        return asset('storage/' . ltrim($this->image, '/'));
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasActivityLog;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brand extends Model
{
    use HasFactory, SoftDeletes, HasAuditLog, HasActivityLog;

    protected static function booted(): void
    {
        static::creating(function (Brand $brand): void {
            if (empty($brand->code)) {
                $slug = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $brand->name ?? 'BRD'));
                $brand->code = 'BRD-' . substr($slug, 0, 6) . '-' . strtoupper(\Illuminate\Support\Str::random(4));
            }
        });
    }

    /**
     * Mass assignable attributes.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'logo',
        'manufacturer',
        'country_of_origin',
        'website',
        'description',
        'status',
        'created_by',
        'updated_by',
    ];

    /**
     * Products belonging to this brand.
     *
     * @return HasMany
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}

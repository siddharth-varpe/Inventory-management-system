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

class Category extends Model
{
    use HasFactory, SoftDeletes, HasAuditLog, HasActivityLog;

    protected static function booted(): void
    {
        static::creating(function (Category $category): void {
            if (empty($category->code)) {
                $slug = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $category->name ?? 'CAT'));
                $category->code = 'CAT-' . substr($slug, 0, 6) . '-' . strtoupper(\Illuminate\Support\Str::random(4));
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
        'parent_id',
        'description',
        'display_order',
        'status',
        'created_by',
        'updated_by',
    ];

    /**
     * Parent category relationship.
     *
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Child categories relationship (recursive).
     *
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->with('children')->orderBy('display_order');
    }

    /**
     * Products belonging to this category.
     *
     * @return HasMany
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}

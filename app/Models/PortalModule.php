<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PortalModule extends Model
{
    use HasFactory;

    /**
     * Mass assignable attributes.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'icon',
        'color_theme',
        'description',
        'is_active',
    ];

    /**
     * Attribute casts.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Permissions for this portal module.
     */
    public function permissions(): HasMany
    {
        return $this->hasMany(PortalPermission::class);
    }

    /**
     * User portal access assignments.
     */
    public function userAccess(): HasMany
    {
        return $this->hasMany(UserPortalAccess::class);
    }
}

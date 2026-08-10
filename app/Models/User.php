<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserStatus;
use App\Traits\HasEnterpriseLogging;
use App\Traits\HasRoles;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasRoles, HasEnterpriseLogging;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'branch_id',
        'department_id',
        'driver_id',
        'last_login_at',
    ];

    /**
     * Driver Master relationship.
     */
    public function driver(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }

    /**
     * Branch relationship.
     */
    public function branch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Department relationship.
     */
    public function department(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Portal access relationship.
     */
    public function portalAccess(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserPortalAccess::class);
    }

    /**
     * Portal login histories relationship.
     */
    public function portalLoginHistories(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PortalLoginHistory::class);
    }

    /**
     * Check if user has active access to a specific portal slug.
     */
    public function hasPortalAccess(string $portalSlug): bool
    {
        // Super admin has access to all active portals
        if ($this->hasRole('super-admin') || $this->hasRole('super_admin')) {
            return true;
        }

        return $this->portalAccess()
            ->whereHas('portalModule', function ($q) use ($portalSlug) {
                $q->where('slug', $portalSlug)->where('is_active', true);
            })
            ->where('status', 'active')
            ->exists();
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'status' => UserStatus::class,
        ];
    }
}

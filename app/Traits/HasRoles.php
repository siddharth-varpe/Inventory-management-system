<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasRoles
{
    /**
     * User roles relationship.
     *
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'model_has_roles', 'model_id', 'role_id')
            ->wherePivot('model_type', static::class);
    }

    /**
     * Check if user has a specific role by slug.
     *
     * @param string|array<int, string> $roles
     * @return bool
     */
    public function hasRole(string|array $roles): bool
    {
        if (is_array($roles)) {
            return $this->roles->pluck('slug')->intersect($roles)->isNotEmpty();
        }

        return $this->roles->contains('slug', $roles);
    }

    /**
     * Check if user has a specific permission.
     *
     * @param string $permissionSlug
     * @return bool
     */
    public function hasPermission(string $permissionSlug): bool
    {
        if ($this->hasRole('super_admin')) {
            return true;
        }

        return $this->roles->flatMap(function (Role $role) {
            return $role->permissions;
        })->contains('slug', $permissionSlug);
    }

    /**
     * Assign role to user.
     *
     * @param Role|string $role
     * @return void
     */
    public function assignRole(Role|string $role): void
    {
        $roleModel = is_string($role) ? Role::where('slug', $role)->firstOrFail() : $role;
        $this->roles()->syncWithoutDetaching([$roleModel->id => ['model_type' => static::class]]);
    }
}

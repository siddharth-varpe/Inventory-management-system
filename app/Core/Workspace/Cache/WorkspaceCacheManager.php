<?php

declare(strict_types=1);

namespace App\Core\Workspace\Cache;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WorkspaceCacheManager
{
    public function getCacheKey(User $user, string $portal): string
    {
        $roleStr = implode('_', $user->roles->pluck('name')->sort()->toArray());
        return "workspace_user_{$user->id}_{$portal}_" . md5($roleStr);
    }

    public function invalidateUserWorkspace(User $user): void
    {
        foreach (['stock', 'organize', 'transport', 'all'] as $portal) {
            $key = $this->getCacheKey($user, $portal);
            Cache::forget($key);
        }

        Log::info("WorkspaceCacheManager: Invalidated cached workspace profiles for User #{$user->id}");
    }
}

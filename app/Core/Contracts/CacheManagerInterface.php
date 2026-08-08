<?php

declare(strict_types=1);

namespace App\Core\Contracts;

interface CacheManagerInterface
{
    /**
     * Invalidate specific domain cache tags/keys.
     */
    public function invalidateDomain(string $domain): void;

    /**
     * Invalidate dashboard analytics caches across all portals.
     */
    public function invalidateAllDashboards(): void;
}

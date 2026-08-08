<?php

declare(strict_types=1);

namespace App\Core\Cache;

use App\Core\Contracts\CacheManagerInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class EnterpriseCacheManager implements CacheManagerInterface
{
    public function invalidateDomain(string $domain): void
    {
        $keys = match ($domain) {
            'inventory', 'stock' => ['dashboard_kpis', 'stock_catalog_stats', 'inventory_valuation'],
            'warehouse', 'organize' => ['warehouse_kpis', 'putaway_queue_count', 'picking_queue_count', 'location_tree'],
            'transport' => ['transport_kpis', 'dispatch_queue'],
            default => ['dashboard_kpis'],
        };

        foreach ($keys as $key) {
            Cache::forget($key);
        }

        Log::info("EnterpriseCacheManager: Invalidated cache keys for domain '{$domain}'");
    }

    public function invalidateAllDashboards(): void
    {
        $this->invalidateDomain('inventory');
        $this->invalidateDomain('warehouse');
        $this->invalidateDomain('transport');
    }
}

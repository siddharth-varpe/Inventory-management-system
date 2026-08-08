<?php

declare(strict_types=1);

namespace App\Services\Settings;

use App\Models\OrganizationSetting;
use Illuminate\Support\Facades\Cache;

class SettingsManager
{
    /**
     * Cache TTL in seconds (1 day).
     */
    protected const CACHE_TTL = 86400;

    /**
     * Get setting value by key.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $settings = Cache::remember('org_settings_cache', self::CACHE_TTL, function () {
            return OrganizationSetting::all()->pluck('value', 'key')->toArray();
        });

        return $settings[$key] ?? $default;
    }

    /**
     * Set / update setting value.
     *
     * @param string $key
     * @param mixed $value
     * @param string $group
     * @return void
     */
    public function set(string $key, mixed $value, string $group = 'general'): void
    {
        OrganizationSetting::updateOrCreate(
            ['key' => $key],
            ['value' => is_array($value) ? json_encode($value) : (string) $value, 'group' => $group]
        );

        Cache::forget('org_settings_cache');
    }
}

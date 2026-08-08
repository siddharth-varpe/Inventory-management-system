<?php

declare(strict_types=1);

use App\Services\Settings\SettingsManager;

if (!function_exists('setting')) {
    /**
     * Helper function to retrieve or set organization settings.
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    function setting(?string $key = null, mixed $default = null): mixed
    {
        /** @var SettingsManager $manager */
        $manager = app(SettingsManager::class);

        if (is_null($key)) {
            return $manager;
        }

        return $manager->get($key, $default);
    }
}

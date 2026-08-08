<?php

declare(strict_types=1);

namespace App\Traits;

use App\Services\Logging\ActivityLogger;

trait HasActivityLog
{
    /**
     * Log user activity helper.
     *
     * @param string $event
     * @param string $description
     * @param string $module
     * @param array<string, mixed> $properties
     * @return void
     */
    protected function logUserActivity(
        string $event,
        string $description = '',
        string $module = 'system',
        array $properties = []
    ): void {
        ActivityLogger::log($event, $description, $module, $properties);
    }
}

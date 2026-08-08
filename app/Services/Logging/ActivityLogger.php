<?php

declare(strict_types=1);

namespace App\Services\Logging;

use App\Models\ActivityLog;

class ActivityLogger
{
    /**
     * Record an activity log entry.
     *
     * @param string $event
     * @param string $description
     * @param string $module
     * @param array<string, mixed> $properties
     * @return void
     */
    public static function log(
        string $event,
        string $description = '',
        string $module = 'system',
        array $properties = []
    ): void {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'event' => $event,
            'module' => $module,
            'description' => $description,
            'properties' => $properties,
            'ip_address' => request()->ip() ?? 'cli',
            'user_agent' => request()->userAgent() ?? 'cli',
            'created_at' => now(),
        ]);
    }
}

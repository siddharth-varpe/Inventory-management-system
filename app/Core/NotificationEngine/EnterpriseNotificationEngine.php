<?php

declare(strict_types=1);

namespace App\Core\NotificationEngine;

use App\Core\Correlation\CorrelationContext;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class EnterpriseNotificationEngine
{
    /**
     * Dispatch role-targeted in-app and activity notifications based on business events.
     */
    public function notifyRole(string $role, string $title, string $message, ?string $link = null, array $metadata = []): void
    {
        $users = User::where('status', 'active')->get();

        foreach ($users as $user) {
            ActivityLog::create([
                'user_id' => $user->id,
                'event' => 'EnterpriseNotification',
                'module' => 'CoreNotificationEngine',
                'description' => "Notification: {$title} - {$message}",
                'properties' => array_merge($metadata, [
                    'role' => $role,
                    'link' => $link,
                    'correlation_id' => CorrelationContext::getCorrelationId(),
                ]),
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'CLI/System',
                'created_at' => now(),
            ]);
        }

        Log::info("EnterpriseNotificationEngine: Dispatched '{$title}' to role '{$role}' [Correlation: " . CorrelationContext::getCorrelationId() . "]");
    }
}

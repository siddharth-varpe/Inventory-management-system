<?php

declare(strict_types=1);

namespace App\Core\EventBus;

use App\Core\Contracts\EventBusInterface;
use App\Core\DTOs\EventPayloadDTO;
use App\Models\ActivityLog;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

class EnterpriseEventBus implements EventBusInterface
{
    public function publish(EventPayloadDTO $event): void
    {
        Log::info("EnterpriseEventBus: Publishing event '{$event->eventName}' from '{$event->sourcePortal}' [Correlation: {$event->correlationId}]");

        // 1. Write structured audit log entry with Correlation ID
        AuditLog::create([
            'user_id' => $event->userId ?? (auth()->id() ?? 1),
            'module' => $event->sourcePortal,
            'action' => $event->eventName,
            'table_name' => 'events',
            'record_id' => 0,
            'old_values' => ['source' => $event->sourcePortal],
            'new_values' => array_merge($event->data, [
                'correlation_id' => $event->correlationId,
                'workflow_id' => $event->workflowId,
            ]),
            'ip_address' => request()->ip() ?? '127.0.0.1',
            'user_agent' => request()->userAgent() ?? 'CLI/System',
            'created_at' => now(),
        ]);

        // 2. Write activity log entry
        ActivityLog::create([
            'user_id' => $event->userId ?? (auth()->id() ?? 1),
            'event' => $event->eventName,
            'module' => $event->sourcePortal,
            'description' => "Event '{$event->eventName}' published [Correlation: {$event->correlationId}]",
            'properties' => $event->toArray(),
            'ip_address' => request()->ip() ?? '127.0.0.1',
            'user_agent' => request()->userAgent() ?? 'CLI/System',
            'created_at' => now(),
        ]);

        // 3. Dispatch to Laravel event bus for subscribers
        Event::dispatch($event->eventName, [$event]);
    }
}

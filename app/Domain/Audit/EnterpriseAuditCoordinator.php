<?php

declare(strict_types=1);

namespace App\Domain\Audit;

use App\Domain\Contracts\AuditCoordinatorInterface;
use App\Domain\DTO\DomainEventData;
use App\Models\ActivityLog;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Log;

class EnterpriseAuditCoordinator implements AuditCoordinatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function recordAudit(DomainEventData $event): void
    {
        Log::info("EnterpriseAuditCoordinator: Recording Audit & Activity Log for Event [{$event->eventType}]");

        try {
            // 1. Immutable Audit Log
            AuditLog::create([
                'user_id' => $event->userId ?? 1,
                'module' => $event->module,
                'action' => $event->eventType,
                'table_name' => $event->module,
                'record_id' => $event->payload['product_id'] ?? ($event->payload['id'] ?? 1),
                'old_values' => json_encode(['status' => 'previous']),
                'new_values' => json_encode($event->payload),
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'ERP Core Orchestrator',
            ]);

            // 2. Activity Log
            ActivityLog::create([
                'user_id' => $event->userId ?? 1,
                'event' => $event->eventType,
                'module' => $event->module,
                'description' => "Domain Event [{$event->eventType}] processed by ERP Core Engine for Ref #{$event->referenceNo}",
                'properties' => json_encode($event->toArray()),
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'ERP Core Orchestrator',
            ]);
        } catch (\Throwable $e) {
            Log::warning("EnterpriseAuditCoordinator: Audit logging non-blocking exception - {$e->getMessage()}");
        }
    }
}

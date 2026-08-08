<?php

declare(strict_types=1);

namespace App\Core\DTOs;

class EventPayloadDTO
{
    public function __construct(
        public string $eventName,
        public string $sourcePortal,
        public string $correlationId,
        public string $workflowId,
        public array $data,
        public ?int $userId = null,
        public ?string $timestamp = null
    ) {
        $this->timestamp = $timestamp ?? now()->toIso8601String();
    }

    public function toArray(): array
    {
        return [
            'event_name' => $this->eventName,
            'source_portal' => $this->sourcePortal,
            'correlation_id' => $this->correlationId,
            'workflow_id' => $this->workflowId,
            'data' => $this->data,
            'user_id' => $this->userId,
            'timestamp' => $this->timestamp,
        ];
    }
}

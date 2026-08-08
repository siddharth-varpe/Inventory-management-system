<?php

declare(strict_types=1);

namespace App\Domain\DTO;

use Illuminate\Support\Str;

class DomainEventData
{
    public string $eventId;
    public string $eventType;
    public string $timestamp;
    public string $module;
    public ?string $referenceNo;
    public ?int $userId;
    public ?int $branchId;
    public array $payload;
    public string $status;

    public function __construct(
        string $eventType,
        string $module,
        array $payload = [],
        ?string $referenceNo = null,
        ?int $userId = null,
        ?int $branchId = null,
        ?string $eventId = null,
        ?string $timestamp = null,
        string $status = 'published'
    ) {
        $this->eventId = $eventId ?? (string) Str::uuid();
        $this->eventType = $eventType;
        $this->module = $module;
        $this->payload = $payload;
        $this->referenceNo = $referenceNo;
        $this->userId = $userId ?? auth()->id();
        $this->branchId = $branchId;
        $this->timestamp = $timestamp ?? now()->toIso8601String();
        $this->status = $status;
    }

    public function toArray(): array
    {
        return [
            'event_id' => $this->eventId,
            'event_type' => $this->eventType,
            'timestamp' => $this->timestamp,
            'module' => $this->module,
            'reference_no' => $this->referenceNo,
            'user_id' => $this->userId,
            'branch_id' => $this->branchId,
            'payload' => $this->payload,
            'status' => $this->status,
        ];
    }
}

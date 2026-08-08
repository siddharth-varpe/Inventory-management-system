<?php

declare(strict_types=1);

namespace App\Domain\Communication\ValueObjects;

use Illuminate\Support\Carbon;

class CommunicationDeliveryResult
{
    public function __construct(
        public readonly bool $success,
        public readonly string $channel,
        public readonly string $messageId,
        public readonly Carbon $sentAt,
        public readonly ?Carbon $deliveredAt = null,
        public readonly ?string $failureReason = null,
        public readonly array $metadata = []
    ) {}

    public static function success(string $channel, string $messageId, array $metadata = []): self
    {
        $now = now();
        return new self(
            success: true,
            channel: $channel,
            messageId: $messageId,
            sentAt: $now,
            deliveredAt: $now,
            failureReason: null,
            metadata: $metadata
        );
    }

    public static function failure(string $channel, string $reason, array $metadata = []): self
    {
        return new self(
            success: false,
            channel: $channel,
            messageId: 'FAILED-' . strtoupper($channel) . '-' . time(),
            sentAt: now(),
            deliveredAt: null,
            failureReason: $reason,
            metadata: $metadata
        );
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'channel' => $this->channel,
            'message_id' => $this->messageId,
            'sent_at' => $this->sentAt->toIso8601String(),
            'delivered_at' => $this->deliveredAt?->toIso8601String(),
            'failure_reason' => $this->failureReason,
            'metadata' => $this->metadata,
        ];
    }
}

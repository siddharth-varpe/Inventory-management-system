<?php

declare(strict_types=1);

namespace App\Domain\Communication;

use App\Models\CommunicationRecord;
use App\Domain\Communication\Channels\ChannelManager;
use App\Domain\Communication\ValueObjects\CommunicationDeliveryResult;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CommunicationDeliveryEngine
{
    public function __construct(
        protected ChannelManager $channelManager,
        protected CommunicationStateMachine $stateMachine
    ) {}

    /**
     * Dispatch Commercial Communication Record over target Channel (Email, WhatsApp).
     */
    public function dispatch(CommunicationRecord $record, string $channel, array $options = [], ?int $userId = null): CommunicationRecord
    {
        $userId = $userId ?? auth()->id() ?? 1;

        if (!$this->channelManager->hasDriver($channel)) {
            throw new InvalidArgumentException("Communication Channel '{$channel}' is not supported.");
        }

        return DB::transaction(function () use ($record, $channel, $options, $userId) {
            // Apply custom message / recipient overrides if provided by salesperson
            if (!empty($options['subject'])) {
                $record->subject = trim($options['subject']);
            }
            if (!empty($options['message_preview'])) {
                $record->message_preview = trim($options['message_preview']);
            }
            if (!empty($options['recipient_email'])) {
                $record->recipient_email = trim($options['recipient_email']);
            }
            if (!empty($options['recipient_mobile'])) {
                $record->recipient_mobile = trim($options['recipient_mobile']);
            }
            $record->preferred_channel = $channel;
            $record->save();

            // Lifecycle Transitions: prepared / draft -> queued -> sending
            if (in_array($record->status, ['draft', 'retry', 'prepared'])) {
                $this->stateMachine->transition($record, 'queued', "Queued for {$channel} dispatch.", $userId);
            }

            $this->stateMachine->transition($record, 'sending', "Transmitting payload to {$channel} gateway...", $userId);

            // Execute Channel Driver
            $driver = $this->channelManager->getDriver($channel);
            $result = $driver->send($record, $options);

            if ($result->success) {
                // Transition sending -> sent -> delivered
                $this->stateMachine->transition($record, 'sent', "Sent via {$channel} gateway (Message ID: {$result->messageId}).", $userId);

                $this->stateMachine->transition($record, 'delivered', "Payload delivered to recipient endpoint.", $userId);

                // Update record delivery timestamps & metadata
                $record->last_sent_at = $result->sentAt;
                $record->last_delivered_at = $result->deliveredAt;

                $meta = $record->metadata ?? [];
                $meta['last_delivery_result'] = $result->toArray();
                $meta['message_id'] = $result->messageId;
                $record->metadata = $meta;
                $record->failure_reason = null;
                $record->save();

                // Write Audit Logs
                $record->logAudit("COMMUNICATION_DISPATCHED", $userId, 'Sales', [
                    'channel' => $channel,
                    'message_id' => $result->messageId,
                    'recipient' => ($channel === 'email' ? $record->recipient_email : $record->recipient_mobile),
                ]);

                return $record;

            } else {
                // Handle Delivery Failure
                $this->stateMachine->transition($record, 'failed', "Delivery failed on {$channel}: {$result->failureReason}", $userId);

                $record->failure_reason = $result->failureReason;
                $record->retry_counter = ((int)$record->retry_counter) + 1;
                $record->save();

                $record->logAudit("COMMUNICATION_FAILED", $userId, 'Sales', [
                    'channel' => $channel,
                    'failure_reason' => $result->failureReason,
                    'retry_counter' => $record->retry_counter,
                ]);

                return $record;
            }
        });
    }

    /**
     * Retry a Failed Communication Record.
     */
    public function retry(CommunicationRecord $record, ?int $userId = null): CommunicationRecord
    {
        $userId = $userId ?? auth()->id() ?? 1;

        if ($record->status !== 'failed') {
            throw new InvalidArgumentException("Communication Record #{$record->communication_number} is not in a failed state (Current status: {$record->status}).");
        }

        DB::transaction(function () use ($record, $userId) {
            $this->stateMachine->transition($record, 'retry', "Retry sequence initiated by User #{$userId}.", $userId);
            $this->stateMachine->transition($record, 'prepared', "Record reset to Prepared for re-transmission.", $userId);

            $record->logAudit("RETRY_INITIATED", $userId, 'Sales', [
                'retry_count' => $record->retry_counter,
                'previous_failure' => $record->failure_reason,
            ]);
        });

        $channel = $record->preferred_channel ?? 'email';
        return $this->dispatch($record, $channel, [], $userId);
    }

    /**
     * Track Recipient Engagement Events (Viewed, Downloaded, Completed).
     */
    public function trackEvent(CommunicationRecord $record, string $event, ?int $userId = null): CommunicationRecord
    {
        $userId = $userId ?? auth()->id() ?? 1;

        return DB::transaction(function () use ($record, $event, $userId) {
            if ($event === 'viewed' && in_array($record->status, ['delivered', 'sent', 'prepared'])) {
                $this->stateMachine->transition($record, 'viewed', "Recipient viewed quotation document.", $userId);
                $record->last_viewed_at = now();
                $record->save();
                $record->logAudit("DOCUMENT_VIEWED", $userId, 'Sales');
            } elseif ($event === 'downloaded' && in_array($record->status, ['viewed', 'delivered'])) {
                $this->stateMachine->transition($record, 'downloaded', "Recipient downloaded PDF quotation proposal.", $userId);
                $record->logAudit("DOCUMENT_DOWNLOADED", $userId, 'Sales');
            } elseif ($event === 'completed' && in_array($record->status, ['downloaded', 'viewed', 'delivered'])) {
                $this->stateMachine->transition($record, 'completed', "Communication cycle completed.", $userId);
                $record->logAudit("COMMUNICATION_COMPLETED", $userId, 'Sales');
            }

            return $record;
        });
    }
}

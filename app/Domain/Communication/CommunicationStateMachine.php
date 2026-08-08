<?php

declare(strict_types=1);

namespace App\Domain\Communication;

use App\Models\CommunicationRecord;
use App\Domain\Communication\Exceptions\InvalidCommunicationStateTransitionException;

class CommunicationStateMachine
{
    /**
     * Map of allowed state transitions.
     */
    protected const ALLOWED_TRANSITIONS = [
        'draft'     => ['prepared', 'queued', 'sending', 'failed'],
        'prepared'  => ['queued', 'ready', 'sending', 'failed'],
        'queued'    => ['ready', 'sending', 'failed'],
        'ready'     => ['sending', 'sent', 'failed'],
        'sending'   => ['sent', 'delivered', 'failed'],
        'sent'      => ['queued', 'sending', 'delivered', 'viewed', 'failed'],
        'delivered' => ['queued', 'sending', 'viewed', 'downloaded', 'completed', 'failed'],
        'viewed'    => ['queued', 'sending', 'downloaded', 'completed', 'failed'],
        'downloaded' => ['completed', 'failed'],
        'failed'    => ['retry', 'prepared', 'queued', 'sending'],
        'retry'     => ['prepared', 'queued', 'sending', 'failed'],
        'completed' => [],
    ];

    /**
     * Check if a transition is valid.
     */
    public function canTransition(string $fromStatus, string $toStatus): bool
    {
        if ($fromStatus === $toStatus) {
            return true;
        }

        $allowed = self::ALLOWED_TRANSITIONS[$fromStatus] ?? [];

        return in_array($toStatus, $allowed, true);
    }

    /**
     * Apply transition to a record or throw an exception.
     */
    public function transition(CommunicationRecord $record, string $toStatus, ?string $notes = null, ?int $userId = null): CommunicationRecord
    {
        $fromStatus = $record->status;

        if (!$this->canTransition($fromStatus, $toStatus)) {
            throw new InvalidCommunicationStateTransitionException($fromStatus, $toStatus, $record->communication_number);
        }

        if ($fromStatus === $toStatus) {
            return $record;
        }

        $record->status = $toStatus;
        if ($toStatus === 'retry') {
            $record->retry_counter += 1;
        }
        $record->save();

        // Record timeline and audit log
        $record->logTimeline("State Transition: {$fromStatus} -> {$toStatus}", $fromStatus, $toStatus, $notes, $userId);
        $record->logAudit("STATUS_CHANGED", $userId, null, [
            'from' => $fromStatus,
            'to' => $toStatus,
            'notes' => $notes,
        ]);

        return $record;
    }
}

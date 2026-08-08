<?php

declare(strict_types=1);

namespace App\Domain\Communication\Contracts;

use App\Models\CommunicationRecord;
use App\Domain\Communication\ValueObjects\CommunicationDeliveryResult;

interface CommunicationChannelInterface
{
    /**
     * Get unique channel name (e.g. 'email', 'whatsapp', 'sms')
     */
    public function getName(): string;

    /**
     * Execute delivery over this communication channel
     */
    public function send(CommunicationRecord $record, array $options = []): CommunicationDeliveryResult;

    /**
     * Validate recipient contact information for this channel
     */
    public function validateRecipient(CommunicationRecord $record): array;
}

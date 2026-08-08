<?php

declare(strict_types=1);

namespace App\Domain\Communication\Channels;

use App\Models\CommunicationRecord;
use App\Domain\Communication\Contracts\CommunicationChannelInterface;
use App\Domain\Communication\ValueObjects\CommunicationDeliveryResult;

class EmailChannelDriver implements CommunicationChannelInterface
{
    public function getName(): string
    {
        return 'email';
    }

    public function validateRecipient(CommunicationRecord $record): array
    {
        $errors = [];
        $email = trim($record->recipient_email ?? '');

        if (empty($email)) {
            $errors[] = "Recipient email address is missing.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Recipient email address '{$email}' is invalid.";
        }

        return $errors;
    }

    public function send(CommunicationRecord $record, array $options = []): CommunicationDeliveryResult
    {
        $validation = $this->validateRecipient($record);
        if (!empty($validation)) {
            return CommunicationDeliveryResult::failure('email', implode(' ', $validation));
        }

        $msgId = 'MSG-EMAIL-' . date('Ymd') . '-' . str_pad((string)rand(1000, 9999), 4, '0', STR_PAD_LEFT);
        $pdfRef = $record->attachment_reference ?? "storage/app/quotations/{$record->enterprise_order_id}_v{$record->document_version}.pdf";

        return CommunicationDeliveryResult::success('email', $msgId, [
            'subject' => $options['subject'] ?? $record->subject,
            'recipient' => $record->recipient_email,
            'pdf_attachment' => $pdfRef,
            'smtp_server' => 'smtp.stockmanager-erp.com',
            'tls' => true,
        ]);
    }
}

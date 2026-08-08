<?php

declare(strict_types=1);

namespace App\Domain\Communication\Channels;

use App\Models\CommunicationRecord;
use App\Domain\Communication\Contracts\CommunicationChannelInterface;
use App\Domain\Communication\ValueObjects\CommunicationDeliveryResult;

class WhatsAppChannelDriver implements CommunicationChannelInterface
{
    public function getName(): string
    {
        return 'whatsapp';
    }

    public function validateRecipient(CommunicationRecord $record): array
    {
        $errors = [];
        $mobile = trim($record->recipient_mobile ?? '');

        if (empty($mobile)) {
            $errors[] = "Recipient mobile / WhatsApp number is missing.";
        } elseif (strlen(preg_replace('/[^0-9]/', '', $mobile)) < 10) {
            $errors[] = "Recipient WhatsApp number '{$mobile}' must contain at least 10 valid digits.";
        }

        return $errors;
    }

    public function send(CommunicationRecord $record, array $options = []): CommunicationDeliveryResult
    {
        $validation = $this->validateRecipient($record);
        if (!empty($validation)) {
            return CommunicationDeliveryResult::failure('whatsapp', implode(' ', $validation));
        }

        $msgId = 'MSG-WA-' . date('Ymd') . '-' . str_pad((string)rand(1000, 9999), 4, '0', STR_PAD_LEFT);
        $pdfRef = $record->attachment_reference ?? "storage/app/quotations/{$record->enterprise_order_id}_v{$record->document_version}.pdf";

        return CommunicationDeliveryResult::success('whatsapp', $msgId, [
            'recipient' => $record->recipient_mobile,
            'pdf_attachment' => $pdfRef,
            'whatsapp_business_account' => 'WABA-STOCKMANAGER-ERP',
            'api_gateway' => 'Cloud API v18.0',
        ]);
    }
}

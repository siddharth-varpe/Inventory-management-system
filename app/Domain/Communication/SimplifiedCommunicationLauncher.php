<?php

declare(strict_types=1);

namespace App\Domain\Communication;

use App\Models\Quotation;
use App\Models\CommunicationRecord;
use Illuminate\Support\Facades\URL;
use InvalidArgumentException;

class SimplifiedCommunicationLauncher
{
    public function __construct(
        protected CommunicationEngineService $cceService
    ) {}

    /**
     * Generate secure, signed PDF quotation proposal link.
     */
    public function generateSecureLink(Quotation $quotation): string
    {
        return URL::signedRoute('sales.quotations.pdf', ['quotation' => $quotation->id]);
    }

    /**
     * Build prefilled Client Mailto Launcher Payload.
     */
    public function buildEmailLauncher(Quotation $quotation, ?string $customSubject = null, ?string $customBody = null): array
    {
        $customer = $quotation->customer;
        $email = trim($customer->email ?? '');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Customer account '{$customer->company_name}' does not have a valid email address.");
        }

        $companyName = "StockManager Enterprise ERP";
        $subject = $customSubject ?? "Quotation Proposal #{$quotation->quotation_number} - {$companyName}";
        $secureLink = $this->generateSecureLink($quotation);
        $customerName = $customer->contact_person ?? $customer->company_name;

        $body = $customBody ?? implode("\n\n", [
            "Dear {$customerName},",
            "Thank you for your enquiry.",
            "Please find your commercial quotation proposal details below:\n" .
            "Quotation Number: {$quotation->quotation_number}\n" .
            "Grand Total: ₹" . number_format((float)$quotation->grand_total, 2),
            "Secure Quotation Link:\n{$secureLink}",
            "If you have any questions, please contact us.",
            "Regards,\n{$companyName}"
        ]);

        $mailtoUrl = "mailto:{$email}?subject=" . rawurlencode($subject) . "&body=" . rawurlencode($body);

        return [
            'channel' => 'email',
            'recipient' => $email,
            'subject' => $subject,
            'body' => $body,
            'secure_link' => $secureLink,
            'launch_url' => $mailtoUrl,
        ];
    }

    /**
     * Build prefilled Client WhatsApp Launcher Payload.
     */
    public function buildWhatsAppLauncher(Quotation $quotation, ?string $customText = null): array
    {
        $customer = $quotation->customer;
        $rawPhone = trim($customer->phone ?? '');
        $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);

        if (empty($cleanPhone) || strlen($cleanPhone) < 10) {
            throw new InvalidArgumentException("Customer account '{$customer->company_name}' does not have a valid mobile / WhatsApp number.");
        }

        if (strlen($cleanPhone) === 10) {
            $cleanPhone = '91' . $cleanPhone;
        }

        $companyName = "StockManager Enterprise ERP";
        $secureLink = $this->generateSecureLink($quotation);
        $customerName = $customer->contact_person ?? $customer->company_name;

        $text = $customText ?? implode("\n\n", [
            "Hello {$customerName},",
            "Thank you for contacting {$companyName}.",
            "Your commercial quotation proposal #{$quotation->quotation_number} has been prepared.\n" .
            "Grand Total: ₹" . number_format((float)$quotation->grand_total, 2),
            "You can view or download your official quotation PDF here:\n{$secureLink}",
            "Please let us know if you have any questions.",
            "Thank you!"
        ]);

        $waUrl = "https://wa.me/{$cleanPhone}?text=" . rawurlencode($text);

        return [
            'channel' => 'whatsapp',
            'recipient' => $rawPhone,
            'message' => $text,
            'secure_link' => $secureLink,
            'launch_url' => $waUrl,
        ];
    }

    /**
     * Execute Communication Launch: Logs CCE record & returns prefilled launcher payload.
     */
    public function launch(Quotation $quotation, string $channel, array $options = [], ?int $userId = null): array
    {
        $userId = $userId ?? auth()->id() ?? 1;
        $channel = strtolower($channel);

        if (!in_array($channel, ['email', 'whatsapp'])) {
            throw new InvalidArgumentException("Unsupported communication channel '{$channel}'.");
        }

        // Get or create CCE record for this quotation
        $cceHistory = $this->cceService->getCommunicationHistory('Quotation', $quotation->id);
        $cceRecord = $cceHistory->first();

        if (!$cceRecord) {
            $cceRecord = $this->cceService->createRecord([
                'customer_id' => $quotation->customer_id,
                'related_document_type' => 'Quotation',
                'related_document_id' => $quotation->id,
                'enterprise_order_id' => $quotation->quotation_number,
                'document_version' => '1.0',
                'subject' => "Commercial Quotation Proposal #{$quotation->quotation_number}",
            ], $userId, 'Sales');
        }

        if ($channel === 'email') {
            $payload = $this->buildEmailLauncher($quotation, $options['subject'] ?? null, $options['message_preview'] ?? null);
            $eventLabel = "Email Client Launched";
            $notes = "Launched default email client for recipient {$payload['recipient']} with secure link.";
        } else {
            $payload = $this->buildWhatsAppLauncher($quotation, $options['message_preview'] ?? null);
            $eventLabel = "WhatsApp Client Launched";
            $notes = "Launched WhatsApp Web/App for recipient {$payload['recipient']} with secure link.";
        }

        // Update CCE record status & metadata
        $cceRecord->status = 'opened';
        $cceRecord->preferred_channel = $channel;
        $cceRecord->subject = $payload['subject'] ?? $cceRecord->subject;
        $cceRecord->message_preview = $payload['body'] ?? $payload['message'] ?? $cceRecord->message_preview;
        $cceRecord->last_sent_at = now();

        $meta = $cceRecord->metadata ?? [];
        $meta['last_launch'] = [
            'channel' => $channel,
            'launch_url' => $payload['launch_url'],
            'secure_link' => $payload['secure_link'],
            'timestamp' => now()->toIso8601String(),
        ];
        $cceRecord->metadata = $meta;
        $cceRecord->save();

        // Write CCE Timeline and Audit Log
        $cceRecord->logTimeline($eventLabel, 'prepared', 'opened', $notes, $userId);
        $cceRecord->logAudit("CLIENT_LAUNCHED", $userId, 'Sales', [
            'channel' => $channel,
            'recipient' => $payload['recipient'],
            'secure_link' => $payload['secure_link'],
        ]);

        $payload['cce_number'] = $cceRecord->communication_number;
        $payload['cce_status'] = $cceRecord->status;

        return $payload;
    }
}

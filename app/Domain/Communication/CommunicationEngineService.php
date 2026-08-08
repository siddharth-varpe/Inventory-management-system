<?php

declare(strict_types=1);

namespace App\Domain\Communication;

use App\Models\Customer;
use App\Models\CommunicationRecord;
use App\Domain\Communication\ValueObjects\CommunicationProfile;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CommunicationEngineService
{
    public function __construct(
        protected CustomerProfileResolver $profileResolver,
        protected CommunicationValidationEngine $validationEngine,
        protected CommunicationStateMachine $stateMachine
    ) {}

    /**
     * Generate globally unique, sequential, immutable Communication Number e.g. COM-2026-000001
     */
    public function generateSequentialNumber(): string
    {
        $year = date('Y');
        $prefix = "COM-{$year}-";

        return DB::transaction(function () use ($prefix) {
            $lastRecord = CommunicationRecord::where('communication_number', 'like', "{$prefix}%")
                ->orderBy('id', 'desc')
                ->lockForUpdate()
                ->first();

            if (!$lastRecord) {
                return $prefix . '000001';
            }

            $lastNum = (int)substr($lastRecord->communication_number, -6);
            $nextNum = str_pad((string)($lastNum + 1), 6, '0', STR_PAD_LEFT);

            return $prefix . $nextNum;
        });
    }

    /**
     * Centralized Communication Record Creation.
     * Resolves Customer Master, validates, generates Communication ID, and logs timeline + audit.
     */
    public function createRecord(array $data, int $userId, string $department = 'Sales'): CommunicationRecord
    {
        $customer = Customer::findOrFail($data['customer_id']);
        $profile = $this->profileResolver->resolve($customer);

        $documentType = $data['related_document_type'] ?? 'GeneralDoc';
        $documentId = (int)($data['related_document_id'] ?? 0);

        $validationErrors = $this->validationEngine->validate($customer, $profile, $documentType, $documentId, $department);

        $commNumber = $this->generateSequentialNumber();

        $recordData = [
            'communication_number' => $commNumber,
            'related_document_type' => $documentType,
            'related_document_id' => $documentId,
            'enterprise_order_id' => $data['enterprise_order_id'] ?? null,
            'customer_id' => $customer->id,
            'customer_name' => $profile->companyName,
            'recipient_email' => $data['recipient_email'] ?? $profile->email,
            'recipient_mobile' => $data['recipient_mobile'] ?? $profile->mobile,
            'preferred_channel' => $data['preferred_channel'] ?? $profile->preferredChannel,
            'document_version' => $data['document_version'] ?? '1.0',
            'attachment_reference' => $data['attachment_reference'] ?? null,
            'subject' => $data['subject'] ?? "Commercial Document Notification - {$documentType} #{$documentId}",
            'message_preview' => $data['message_preview'] ?? null,
            'status' => 'draft',
            'created_by' => $userId,
            'created_department' => $department,
            'failure_reason' => !empty($validationErrors) ? implode(' | ', $validationErrors) : null,
            'retry_counter' => 0,
            'metadata' => array_merge($data['metadata'] ?? [], [
                'customer_profile' => $profile->toArray(),
                'validation_errors' => $validationErrors,
                'validation_passed' => empty($validationErrors),
            ]),
        ];

        return DB::transaction(function () use ($recordData, $validationErrors, $userId, $department) {
            $record = CommunicationRecord::create($recordData);

            // Log Initial Creation Timeline
            $statusNote = empty($validationErrors) ? "Communication Record created in Draft state." : "Validation warnings logged on creation.";
            $record->logTimeline("Communication Created", null, "draft", $statusNote, $userId);

            // Log Initial Creation Audit Log
            $record->logAudit("RECORD_CREATED", $userId, $department, [
                'communication_number' => $record->communication_number,
                'validation_passed' => empty($validationErrors),
                'errors' => $validationErrors,
            ]);

            return $record;
        });
    }

    /**
     * Get Record by ID or Communication Number.
     */
    public function getRecord(int|string $id): CommunicationRecord
    {
        if (is_numeric($id)) {
            return CommunicationRecord::with(['customer', 'creator', 'timelines', 'auditLogs'])->findOrFail((int)$id);
        }

        return CommunicationRecord::with(['customer', 'creator', 'timelines', 'auditLogs'])
            ->where('communication_number', $id)
            ->firstOrFail();
    }

    /**
     * Resolve Customer Master Profile.
     */
    public function resolveCustomerProfile(Customer $customer): CommunicationProfile
    {
        return $this->profileResolver->resolve($customer);
    }

    /**
     * Validate Recipient against Customer Master & Business Rules.
     */
    public function validateRecipient(Customer $customer, string $documentType, int $documentId, string $department = 'Sales'): array
    {
        $profile = $this->profileResolver->resolve($customer);
        return $this->validationEngine->validate($customer, $profile, $documentType, $documentId, $department);
    }

    /**
     * Transition Record from Draft/Retry to Prepared.
     */
    public function prepareCommunication(CommunicationRecord $record, ?string $subject = null, ?string $preview = null, ?int $userId = null): CommunicationRecord
    {
        if ($subject) {
            $record->subject = $subject;
        }
        if ($preview) {
            $record->message_preview = $preview;
        }
        $record->save();

        return $this->stateMachine->transition($record, 'prepared', "Communication content and attachments prepared successfully.", $userId);
    }

    /**
     * Execute State Machine Lifecycle Transition.
     */
    public function updateStatus(CommunicationRecord $record, string $newStatus, ?string $notes = null, ?int $userId = null): CommunicationRecord
    {
        return $this->stateMachine->transition($record, $newStatus, $notes, $userId);
    }

    /**
     * Get Historical Communication Records for a Document Reference.
     */
    public function getCommunicationHistory(string $documentType, int $documentId): Collection
    {
        return CommunicationRecord::with(['creator', 'timelines'])
            ->where('related_document_type', $documentType)
            ->where('related_document_id', $documentId)
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * Archive Communication Record for compliance.
     */
    public function archiveCommunication(CommunicationRecord $record, ?int $userId = null): CommunicationRecord
    {
        $metadata = $record->metadata ?? [];
        $metadata['archived_at'] = now()->toIso8601String();
        $metadata['archived_by'] = $userId ?? auth()->id();

        $record->metadata = $metadata;
        $record->save();

        $record->logTimeline("Communication Archived", $record->status, $record->status, "Communication record archived for historical compliance.", $userId);
        $record->logAudit("RECORD_ARCHIVED", $userId, null, ['archived_at' => $metadata['archived_at']]);

        return $record;
    }
}

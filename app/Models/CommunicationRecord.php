<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommunicationRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'communication_number',
        'related_document_type',
        'related_document_id',
        'enterprise_order_id',
        'customer_id',
        'customer_name',
        'recipient_email',
        'recipient_mobile',
        'preferred_channel',
        'document_version',
        'attachment_reference',
        'subject',
        'message_preview',
        'status',
        'created_by',
        'created_department',
        'failure_reason',
        'retry_counter',
        'metadata',
        'last_sent_at',
        'last_delivered_at',
        'last_viewed_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'retry_counter' => 'integer',
        'last_sent_at' => 'datetime',
        'last_delivered_at' => 'datetime',
        'last_viewed_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function timelines(): HasMany
    {
        return $this->hasMany(CommunicationTimeline::class, 'communication_record_id')->orderBy('id', 'asc');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(CommunicationAuditLog::class, 'communication_record_id')->orderBy('id', 'asc');
    }

    /**
     * Add timeline entry.
     */
    public function logTimeline(string $eventName, ?string $fromStatus, ?string $toStatus, ?string $notes = null, ?int $userId = null): CommunicationTimeline
    {
        return $this->timelines()->create([
            'event_name' => $eventName,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'notes' => $notes,
            'performed_by' => $userId ?? auth()->id(),
            'created_at' => now(),
        ]);
    }

    /**
     * Add immutable audit log entry.
     */
    public function logAudit(string $action, ?int $userId = null, ?string $department = null, ?array $payload = null): CommunicationAuditLog
    {
        return $this->auditLogs()->create([
            'action' => $action,
            'performed_by' => $userId ?? auth()->id(),
            'department' => $department ?? $this->created_department,
            'payload' => $payload,
            'created_at' => now(),
        ]);
    }
}

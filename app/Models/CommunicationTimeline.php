<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunicationTimeline extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'communication_record_id',
        'event_name',
        'from_status',
        'to_status',
        'notes',
        'performed_by',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function record(): BelongsTo
    {
        return $this->belongsTo(CommunicationRecord::class, 'communication_record_id');
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}

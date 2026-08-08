<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DispatchManifest extends Model
{
    protected $fillable = [
        'manifest_number',
        'transport_trip_id',
        'vehicle_id',
        'driver_id',
        'package_count',
        'total_weight_kg',
        'total_volume_m3',
        'destination_summary',
        'dispatch_timestamp',
        'checklist_result',
        'warehouse_completed_at',
        'transport_accepted_at',
        'created_by',
        'warehouse_supervisor_name',
        'status',
    ];

    protected $casts = [
        'dispatch_timestamp' => 'datetime',
        'warehouse_completed_at' => 'datetime',
        'transport_accepted_at' => 'datetime',
        'checklist_result' => 'array',
        'total_weight_kg' => 'decimal:2',
        'total_volume_m3' => 'decimal:2',
    ];

    public function transportTrip(): BelongsTo
    {
        return $this->belongsTo(TransportTrip::class, 'transport_trip_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }

    /**
     * Assigned Orders Collection (Future-proof architecture for 1-to-many multi-order manifests)
     */
    public function transportRequests(): HasMany
    {
        return $this->hasMany(TransportRequest::class, 'dispatch_manifest_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isLocked(): bool
    {
        return in_array($this->status, ['locked', 'dispatched', 'completed']);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'created' => 'bg-info-subtle text-info border-info-subtle',
            'locked' => 'bg-purple-subtle text-purple border-purple-subtle',
            'dispatched' => 'bg-success-subtle text-success border-success-subtle',
            'completed' => 'bg-secondary-subtle text-secondary border-secondary-subtle',
            default => 'bg-info-subtle text-info border-info-subtle',
        };
    }
}

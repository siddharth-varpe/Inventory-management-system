<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TransportTrip extends Model
{
    protected $fillable = [
        'trip_number',
        'vehicle_id',
        'driver_id',
        'dispatch_manifest_id',
        'planned_departure_at',
        'dispatched_at',
        'closed_at',
        'closed_by',
        'expected_delivery_date',
        'total_package_count',
        'total_weight_kg',
        'total_volume_m3',
        'destination_city',
        'status',
        'created_by',
    ];

    protected $casts = [
        'planned_departure_at' => 'datetime',
        'dispatched_at' => 'datetime',
        'closed_at' => 'datetime',
        'expected_delivery_date' => 'date',
        'total_weight_kg' => 'decimal:2',
        'total_volume_m3' => 'decimal:2',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }

    public function dispatchManifest(): BelongsTo
    {
        return $this->belongsTo(DispatchManifest::class, 'dispatch_manifest_id');
    }

    /**
     * Assigned Orders Collection (Future-proof architecture for 1-to-many multi-order trips)
     */
    public function transportRequests(): HasMany
    {
        return $this->hasMany(TransportRequest::class, 'transport_trip_id');
    }

    public function dispatchChecklists(): HasMany
    {
        return $this->hasMany(DispatchChecklist::class, 'transport_trip_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function closedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'created' => 'Trip Created',
            'ready' => 'Trip Ready for Dispatch',
            'dispatched' => 'Dispatched (In Transit)',
            'pending_closure' => 'Pending Trip Closure',
            'closed', 'completed' => 'Trip Officially Closed',
            'cancelled' => 'Trip Cancelled',
            default => 'Trip Created',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'created' => 'bg-info-subtle text-info border-info-subtle',
            'ready' => 'bg-primary-subtle text-primary border-primary-subtle',
            'dispatched' => 'bg-success-subtle text-success border-success-subtle',
            'pending_closure' => 'bg-warning text-dark',
            'closed', 'completed' => 'bg-secondary-subtle text-secondary border-secondary-subtle',
            'cancelled' => 'bg-danger-subtle text-danger border-danger-subtle',
            default => 'bg-info-subtle text-info border-info-subtle',
        };
    }
}

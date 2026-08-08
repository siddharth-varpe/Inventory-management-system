<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverVehicleAssignment extends Model
{
    protected $table = 'driver_vehicle_assignments';

    protected $fillable = [
        'assignment_number',
        'transport_request_id',
        'sales_order_id',
        'enterprise_order_id',
        'driver_id',
        'vehicle_id',
        'assigned_by',
        'assigned_at',
        'status',
        'reassignment_reason',
        'instructions',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
    ];

    public function transportRequest(): BelongsTo
    {
        return $this->belongsTo(TransportRequest::class, 'transport_request_id');
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function assignedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return match(strtolower($this->status ?? 'active')) {
            'active' => 'Active',
            'reassigned' => 'Reassigned',
            'released' => 'Released',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status ?? 'Active'),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match(strtolower($this->status ?? 'active')) {
            'active' => 'bg-success-subtle text-success border border-success-subtle',
            'reassigned' => 'bg-warning-subtle text-warning border border-warning-subtle',
            'released' => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
            'cancelled' => 'bg-danger-subtle text-danger border border-danger-subtle',
            default => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
        };
    }
}

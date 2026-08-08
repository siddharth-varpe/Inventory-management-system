<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Driver extends Model
{
    protected $table = 'drivers';

    protected $fillable = [
        'driver_code',
        'driver_name',
        'employee_id',
        'phone_number',
        'email',
        'date_of_birth',
        'address',
        'emergency_contact_name',
        'emergency_contact_number',
        'emergency_contact',
        'photo_url',
        'joining_date',
        'license_class',
        'driving_license_number',
        'license_expiry_date',
        'medical_certificate_date',
        'medical_certificate_expiry',
        'status',
        'suspended_by',
        'suspended_at',
        'suspension_reason',
        'deactivated_at',
        'current_assignment',
        'performance_rating',
        'notes',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'joining_date' => 'date',
        'license_expiry_date' => 'date',
        'medical_certificate_date' => 'date',
        'medical_certificate_expiry' => 'date',
        'suspended_at' => 'datetime',
        'deactivated_at' => 'datetime',
        'performance_rating' => 'decimal:2',
    ];

    /**
     * Permanent Transport Trip Assignments
     */
    public function trips(): HasMany
    {
        return $this->hasMany(TransportTrip::class, 'driver_id');
    }

    /**
     * User who executed suspension
     */
    public function suspendedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'suspended_by');
    }

    public function isAvailable(): bool
    {
        return strtolower($this->status ?? '') === 'available';
    }

    public function isSuspended(): bool
    {
        return strtolower($this->status ?? '') === 'suspended';
    }

    public function isInactive(): bool
    {
        return strtolower($this->status ?? '') === 'inactive';
    }

    public function isLicenseExpired(): bool
    {
        return $this->license_expiry_date && $this->license_expiry_date->isPast();
    }

    public function isLicenseExpiringSoon(): bool
    {
        if (!$this->license_expiry_date) {
            return false;
        }
        return !$this->isLicenseExpired() && $this->license_expiry_date->diffInDays(now()) <= 30;
    }

    public function getStatusLabelAttribute(): string
    {
        return match(strtolower($this->status ?? 'available')) {
            'available' => 'Available',
            'on_trip', 'on_delivery' => 'On Delivery',
            'leave', 'on_leave' => 'On Leave',
            'suspended' => 'Suspended',
            'inactive', 'off_duty' => 'Inactive',
            default => ucfirst($this->status ?? 'Available'),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match(strtolower($this->status ?? 'available')) {
            'available' => 'bg-success-subtle text-success border border-success-subtle',
            'on_trip', 'on_delivery' => 'bg-primary-subtle text-primary border border-primary-subtle',
            'leave', 'on_leave' => 'bg-info-subtle text-info border border-info-subtle',
            'suspended' => 'bg-danger text-white',
            'inactive', 'off_duty' => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
            default => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
        };
    }
}

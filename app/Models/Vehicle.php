<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Vehicle extends Model
{
    protected $table = 'vehicles';

    protected $fillable = [
        'vehicle_code',
        'vehicle_number',
        'vehicle_type',
        'manufacturer',
        'model',
        'manufacturing_year',
        'color',
        'fuel_type',
        'purchase_date',
        'load_capacity_kg',
        'volume_capacity_m3',
        'insurance_policy_number',
        'insurance_expiry_date',
        'fitness_certificate_number',
        'fitness_expiry_date',
        'permit_number',
        'permit_expiry_date',
        'rc_number',
        'puc_expiry_date',
        'current_odometer_km',
        'last_service_date',
        'next_service_due_date',
        'current_location',
        'status',
        'maintenance_status',
        'notes',
        'maintenance_reason',
        'maintenance_start_date',
        'maintenance_expected_completion',
        'maintenance_notes',
        'breakdown_reason',
        'breakdown_at',
        'breakdown_notes',
        'deactivated_at',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'insurance_expiry_date' => 'date',
        'fitness_expiry_date' => 'date',
        'permit_expiry_date' => 'date',
        'puc_expiry_date' => 'date',
        'last_service_date' => 'date',
        'next_service_due_date' => 'date',
        'maintenance_start_date' => 'date',
        'maintenance_expected_completion' => 'date',
        'breakdown_at' => 'datetime',
        'deactivated_at' => 'datetime',
        'load_capacity_kg' => 'decimal:2',
        'volume_capacity_m3' => 'decimal:2',
        'current_odometer_km' => 'integer',
        'manufacturing_year' => 'integer',
    ];

    public function trips(): HasMany
    {
        return $this->hasMany(TransportTrip::class, 'vehicle_id');
    }

    public function isAvailable(): bool
    {
        return strtolower($this->status ?? '') === 'available' 
            && !in_array(strtolower($this->status ?? ''), ['maintenance', 'breakdown', 'inactive']);
    }

    public function isUnderMaintenance(): bool
    {
        return strtolower($this->status ?? '') === 'maintenance';
    }

    public function isBreakdown(): bool
    {
        return strtolower($this->status ?? '') === 'breakdown';
    }

    public function isInactive(): bool
    {
        return strtolower($this->status ?? '') === 'inactive';
    }

    /**
     * Calculate Document Compliance Status dynamically
     */
    public function getDocumentComplianceStatus(mixed $date): string
    {
        if (!$date) {
            return 'Not Recorded';
        }

        $cDate = $date instanceof Carbon ? $date : Carbon::parse((string)$date);

        if ($cDate->isPast()) {
            return 'Expired';
        }

        $days = (int) now()->diffInDays($cDate, false);
        if ($days >= 0 && $days <= 30) {
            return 'Expiring Soon';
        }

        return 'Valid';
    }

    public function getInsuranceStatusAttribute(): string
    {
        return $this->getDocumentComplianceStatus($this->insurance_expiry_date);
    }

    public function getFitnessStatusAttribute(): string
    {
        return $this->getDocumentComplianceStatus($this->fitness_expiry_date);
    }

    public function getPucStatusAttribute(): string
    {
        return $this->getDocumentComplianceStatus($this->puc_expiry_date);
    }

    public function getPermitStatusAttribute(): string
    {
        return $this->getDocumentComplianceStatus($this->permit_expiry_date);
    }

    public function hasExpiringOrExpiredDocuments(): bool
    {
        $statuses = [
            $this->insurance_status,
            $this->fitness_status,
            $this->puc_status,
            $this->permit_status,
        ];
        return in_array('Expired', $statuses, true) || in_array('Expiring Soon', $statuses, true);
    }

    public function getStatusLabelAttribute(): string
    {
        return match(strtolower($this->status ?? 'available')) {
            'available' => 'Available',
            'reserved' => 'Reserved for Trip',
            'on_trip' => 'On Delivery Trip',
            'maintenance' => 'Maintenance',
            'breakdown' => 'Breakdown',
            'inactive', 'unavailable' => 'Inactive',
            default => ucfirst($this->status ?? 'Available'),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match(strtolower($this->status ?? 'available')) {
            'available' => 'bg-success-subtle text-success border border-success-subtle',
            'reserved' => 'bg-info-subtle text-info border border-info-subtle',
            'on_trip' => 'bg-primary-subtle text-primary border border-primary-subtle',
            'maintenance' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
            'breakdown' => 'bg-danger text-white',
            'inactive', 'unavailable' => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
            default => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
        };
    }
}

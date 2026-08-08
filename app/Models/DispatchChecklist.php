<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DispatchChecklist extends Model
{
    protected $fillable = [
        'transport_request_id',
        'transport_trip_id',
        'vehicle_inspected',
        'packages_loaded',
        'package_count_verified',
        'labels_verified',
        'delivery_documents_verified',
        'vehicle_doors_sealed',
        'driver_documents_verified',
        'loading_completed',
        'supervisor_approved',
        'is_completed',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'vehicle_inspected' => 'boolean',
        'packages_loaded' => 'boolean',
        'package_count_verified' => 'boolean',
        'labels_verified' => 'boolean',
        'delivery_documents_verified' => 'boolean',
        'vehicle_doors_sealed' => 'boolean',
        'driver_documents_verified' => 'boolean',
        'loading_completed' => 'boolean',
        'supervisor_approved' => 'boolean',
        'is_completed' => 'boolean',
        'verified_at' => 'datetime',
    ];

    public function transportRequest(): BelongsTo
    {
        return $this->belongsTo(TransportRequest::class, 'transport_request_id');
    }

    public function transportTrip(): BelongsTo
    {
        return $this->belongsTo(TransportTrip::class, 'transport_trip_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function verifyAllCompleted(): bool
    {
        $allChecked = $this->vehicle_inspected &&
                      $this->packages_loaded &&
                      $this->package_count_verified &&
                      $this->labels_verified &&
                      $this->delivery_documents_verified &&
                      $this->vehicle_doors_sealed &&
                      $this->driver_documents_verified &&
                      $this->loading_completed &&
                      $this->supervisor_approved;

        if ($allChecked && !$this->is_completed) {
            $this->update([
                'is_completed' => true,
                'verified_by' => auth()->id() ?? $this->verified_by ?? 1,
                'verified_at' => now(),
            ]);
        }

        return $allChecked;
    }
}

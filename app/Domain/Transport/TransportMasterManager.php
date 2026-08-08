<?php

declare(strict_types=1);

namespace App\Domain\Transport;

use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class TransportMasterManager
{
    /**
     * Generate Permanent Immutable Driver ID (DRV-000001)
     */
    public function generateDriverCode(): string
    {
        $maxId = (int) (DB::table('drivers')->max('id') ?? 0);
        $seq = $maxId + 1;

        do {
            $code = 'DRV-' . str_pad((string)$seq, 6, '0', STR_PAD_LEFT);
            $exists = Driver::where('driver_code', $code)->exists();
            if ($exists) {
                $seq++;
            }
        } while ($exists);

        return $code;
    }

    /**
     * Generate Permanent Immutable Vehicle ID (VEH-000001)
     */
    public function generateVehicleCode(): string
    {
        $maxId = (int) (DB::table('vehicles')->max('id') ?? 0);
        $seq = $maxId + 1;

        do {
            $code = 'VEH-' . str_pad((string)$seq, 6, '0', STR_PAD_LEFT);
            $exists = Vehicle::where('vehicle_code', $code)->exists();
            if ($exists) {
                $seq++;
            }
        } while ($exists);

        return $code;
    }

    /**
     * Normalize Indian Mobile Number
     */
    public function normalizePhoneNumber(string $phone): string
    {
        $digits = preg_replace('/[^\d]/', '', $phone);
        
        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            $digits = substr($digits, 2);
        }

        if (strlen($digits) !== 10) {
            throw new InvalidArgumentException("Mobile number must be a valid 10-digit Indian mobile number.");
        }

        return '+91 ' . substr($digits, 0, 5) . ' ' . substr($digits, 5, 5);
    }

    /**
     * Normalize Indian Registration Number
     */
    public function normalizeRegistrationNumber(string $reg): string
    {
        $clean = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $reg));
        if (strlen($clean) < 6 || strlen($clean) > 13) {
            throw new InvalidArgumentException("Vehicle Registration Number must be a valid legal registration format.");
        }
        return $clean;
    }

    /**
     * Register New Driver Master Data (Phase 1)
     */
    public function registerDriver(array $data, int $operatorId): Driver
    {
        return DB::transaction(function () use ($data, $operatorId) {
            $normalizedPhone = $this->normalizePhoneNumber($data['phone_number']);
            $existingPhone = Driver::where('phone_number', $normalizedPhone)
                ->where('status', '!=', 'inactive')
                ->exists();
            if ($existingPhone) {
                throw new InvalidArgumentException("Mobile number {$normalizedPhone} is already registered to an active driver.");
            }

            $licenseNumber = strtoupper(trim($data['driving_license_number']));
            $existingLicense = Driver::where('driving_license_number', $licenseNumber)->exists();
            if ($existingLicense) {
                throw new InvalidArgumentException("Driving License Number {$licenseNumber} is already registered.");
            }

            $driverCode = $this->generateDriverCode();

            $driver = Driver::create([
                'driver_code' => $driverCode,
                'driver_name' => trim($data['driver_name']),
                'employee_id' => $data['employee_id'] ?? ('EMP-DRV-' . str_pad((string)(Driver::count() + 1), 4, '0', STR_PAD_LEFT)),
                'phone_number' => $normalizedPhone,
                'email' => isset($data['email']) && filled($data['email']) ? trim($data['email']) : null,
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'address' => isset($data['address']) && filled($data['address']) ? trim($data['address']) : null,
                'emergency_contact_name' => $data['emergency_contact_name'] ?? null,
                'emergency_contact_number' => isset($data['emergency_contact_number']) && filled($data['emergency_contact_number']) 
                    ? $this->normalizePhoneNumber($data['emergency_contact_number']) 
                    : null,
                'emergency_contact' => $data['emergency_contact'] ?? ($data['emergency_contact_number'] ?? null),
                'photo_url' => $data['photo_url'] ?? null,
                'joining_date' => $data['joining_date'] ?? now()->format('Y-m-d'),
                'license_class' => $data['license_class'] ?? 'Heavy Commercial (HMV)',
                'driving_license_number' => $licenseNumber,
                'license_expiry_date' => $data['license_expiry_date'],
                'medical_certificate_date' => $data['medical_certificate_date'] ?? null,
                'medical_certificate_expiry' => $data['medical_certificate_expiry'] ?? null,
                'status' => 'available',
                'current_assignment' => 'Available for Assignment',
                'performance_rating' => 5.00,
                'notes' => $data['notes'] ?? null,
            ]);

            AuditLog::create([
                'user_id' => $operatorId,
                'module' => 'Driver Management',
                'action' => 'Driver Created',
                'table_name' => 'drivers',
                'record_id' => $driver->id,
                'old_values' => null,
                'new_values' => json_encode($driver->toArray()),
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System',
            ]);

            Log::info("TransportMasterManager: Permanent Driver {$driver->driver_name} ({$driver->driver_code}) registered by user {$operatorId}.");

            return $driver;
        });
    }

    /**
     * Update Driver Master Data (Phase 1)
     */
    public function updateDriver(Driver $driver, array $data, int $operatorId): Driver
    {
        return DB::transaction(function () use ($driver, $data, $operatorId) {
            $oldValues = $driver->toArray();

            if (isset($data['phone_number']) && filled($data['phone_number'])) {
                $normalizedPhone = $this->normalizePhoneNumber($data['phone_number']);
                if ($normalizedPhone !== $driver->phone_number) {
                    $exists = Driver::where('phone_number', $normalizedPhone)
                        ->where('id', '!=', $driver->id)
                        ->where('status', '!=', 'inactive')
                        ->exists();
                    if ($exists) {
                        throw new InvalidArgumentException("Mobile number {$normalizedPhone} is already in use by another active driver.");
                    }
                    $data['phone_number'] = $normalizedPhone;
                }
            }

            if (isset($data['driving_license_number']) && filled($data['driving_license_number'])) {
                $licenseNumber = strtoupper(trim($data['driving_license_number']));
                if ($licenseNumber !== $driver->driving_license_number) {
                    $exists = Driver::where('driving_license_number', $licenseNumber)
                        ->where('id', '!=', $driver->id)
                        ->exists();
                    if ($exists) {
                        throw new InvalidArgumentException("Driving License Number {$licenseNumber} is already in use.");
                    }
                    $data['driving_license_number'] = $licenseNumber;
                }
            }

            if (isset($data['emergency_contact_number']) && filled($data['emergency_contact_number'])) {
                $data['emergency_contact_number'] = $this->normalizePhoneNumber($data['emergency_contact_number']);
            }

            unset($data['driver_code'], $data['id'], $data['created_at']);

            $driver->update($data);

            AuditLog::create([
                'user_id' => $operatorId,
                'module' => 'Driver Management',
                'action' => 'Driver Updated',
                'table_name' => 'drivers',
                'record_id' => $driver->id,
                'old_values' => json_encode($oldValues),
                'new_values' => json_encode($driver->toArray()),
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System',
            ]);

            return $driver;
        });
    }

    /**
     * Activate Driver
     */
    public function activateDriver(Driver $driver, int $operatorId): Driver
    {
        return DB::transaction(function () use ($driver, $operatorId) {
            $old = $driver->toArray();

            $driver->update([
                'status' => 'available',
                'suspended_by' => null,
                'suspended_at' => null,
                'suspension_reason' => null,
                'deactivated_at' => null,
                'current_assignment' => 'Available for Assignment',
            ]);

            AuditLog::create([
                'user_id' => $operatorId,
                'module' => 'Driver Management',
                'action' => 'Driver Activated',
                'table_name' => 'drivers',
                'record_id' => $driver->id,
                'old_values' => json_encode($old),
                'new_values' => json_encode($driver->toArray()),
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System',
            ]);

            return $driver;
        });
    }

    /**
     * Deactivate Driver (Soft Deactivation - Record Persisted)
     */
    public function deactivateDriver(Driver $driver, int $operatorId): Driver
    {
        return DB::transaction(function () use ($driver, $operatorId) {
            $old = $driver->toArray();

            $driver->update([
                'status' => 'inactive',
                'deactivated_at' => now(),
                'current_assignment' => 'Deactivated / Off Roster',
            ]);

            AuditLog::create([
                'user_id' => $operatorId,
                'module' => 'Driver Management',
                'action' => 'Driver Deactivated',
                'table_name' => 'drivers',
                'record_id' => $driver->id,
                'old_values' => json_encode($old),
                'new_values' => json_encode($driver->toArray()),
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System',
            ]);

            return $driver;
        });
    }

    /**
     * Suspend Driver (Requires Reason)
     */
    public function suspendDriver(Driver $driver, string $reason, int $operatorId): Driver
    {
        if (empty(trim($reason))) {
            throw new InvalidArgumentException("A suspension reason is mandatory to suspend a driver.");
        }

        return DB::transaction(function () use ($driver, $reason, $operatorId) {
            $old = $driver->toArray();

            $driver->update([
                'status' => 'suspended',
                'suspended_by' => $operatorId,
                'suspended_at' => now(),
                'suspension_reason' => trim($reason),
                'current_assignment' => 'Suspended from Duty',
            ]);

            AuditLog::create([
                'user_id' => $operatorId,
                'module' => 'Driver Management',
                'action' => 'Driver Suspended',
                'table_name' => 'drivers',
                'record_id' => $driver->id,
                'old_values' => json_encode($old),
                'new_values' => json_encode($driver->toArray()),
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System',
            ]);

            return $driver;
        });
    }

    /**
     * Register New Vehicle Master Data (Phase 2)
     */
    public function registerVehicle(array $data, int $operatorId): Vehicle
    {
        return DB::transaction(function () use ($data, $operatorId) {
            $regNumber = $this->normalizeRegistrationNumber($data['vehicle_number']);
            $existingReg = Vehicle::where('vehicle_number', $regNumber)->exists();
            if ($existingReg) {
                throw new InvalidArgumentException("Vehicle Registration Number {$regNumber} is already registered.");
            }

            if ((float)$data['load_capacity_kg'] <= 0) {
                throw new InvalidArgumentException("Vehicle Load Capacity must be a positive numeric value in kg.");
            }

            $vehicleCode = $this->generateVehicleCode();

            $vehicle = Vehicle::create([
                'vehicle_code' => $vehicleCode,
                'vehicle_number' => $regNumber,
                'vehicle_type' => $data['vehicle_type'],
                'manufacturer' => trim($data['manufacturer']),
                'model' => trim($data['model']),
                'manufacturing_year' => isset($data['manufacturing_year']) ? (int)$data['manufacturing_year'] : null,
                'color' => $data['color'] ?? null,
                'fuel_type' => $data['fuel_type'] ?? 'Diesel',
                'purchase_date' => $data['purchase_date'] ?? null,
                'load_capacity_kg' => (float)$data['load_capacity_kg'],
                'volume_capacity_m3' => isset($data['volume_capacity_m3']) ? (float)$data['volume_capacity_m3'] : 15.00,
                'insurance_policy_number' => $data['insurance_policy_number'] ?? null,
                'insurance_expiry_date' => $data['insurance_expiry_date'] ?? null,
                'fitness_certificate_number' => $data['fitness_certificate_number'] ?? null,
                'fitness_expiry_date' => $data['fitness_expiry_date'] ?? null,
                'permit_number' => $data['permit_number'] ?? null,
                'permit_expiry_date' => $data['permit_expiry_date'] ?? null,
                'rc_number' => $data['rc_number'] ?? null,
                'puc_expiry_date' => $data['puc_expiry_date'] ?? null,
                'current_odometer_km' => isset($data['current_odometer_km']) ? (int)$data['current_odometer_km'] : 0,
                'last_service_date' => $data['last_service_date'] ?? null,
                'next_service_due_date' => $data['next_service_due_date'] ?? null,
                'current_location' => 'Central Warehouse Freight Yard',
                'status' => 'available',
                'maintenance_status' => 'Good',
                'notes' => $data['notes'] ?? null,
            ]);

            AuditLog::create([
                'user_id' => $operatorId,
                'module' => 'Vehicle Management',
                'action' => 'Vehicle Created',
                'table_name' => 'vehicles',
                'record_id' => $vehicle->id,
                'old_values' => null,
                'new_values' => json_encode($vehicle->toArray()),
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System',
            ]);

            Log::info("TransportMasterManager: Permanent Vehicle {$vehicle->vehicle_number} ({$vehicle->vehicle_code}) registered by user {$operatorId}.");

            return $vehicle;
        });
    }

    /**
     * Update Vehicle Master Data (Phase 2)
     */
    public function updateVehicle(Vehicle $vehicle, array $data, int $operatorId): Vehicle
    {
        return DB::transaction(function () use ($vehicle, $data, $operatorId) {
            $oldValues = $vehicle->toArray();

            if (isset($data['vehicle_number']) && filled($data['vehicle_number'])) {
                $regNumber = $this->normalizeRegistrationNumber($data['vehicle_number']);
                if ($regNumber !== $vehicle->vehicle_number) {
                    $exists = Vehicle::where('vehicle_number', $regNumber)
                        ->where('id', '!=', $vehicle->id)
                        ->exists();
                    if ($exists) {
                        throw new InvalidArgumentException("Registration Number {$regNumber} is already in use by another vehicle.");
                    }
                    $data['vehicle_number'] = $regNumber;
                }
            }

            if (isset($data['load_capacity_kg']) && (float)$data['load_capacity_kg'] <= 0) {
                throw new InvalidArgumentException("Load capacity must be a positive numeric value in kg.");
            }

            unset($data['vehicle_code'], $data['id'], $data['created_at']);

            $vehicle->update($data);

            AuditLog::create([
                'user_id' => $operatorId,
                'module' => 'Vehicle Management',
                'action' => 'Vehicle Updated',
                'table_name' => 'vehicles',
                'record_id' => $vehicle->id,
                'old_values' => json_encode($oldValues),
                'new_values' => json_encode($vehicle->toArray()),
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System',
            ]);

            Log::info("TransportMasterManager: Vehicle {$vehicle->vehicle_code} updated by user {$operatorId}.");

            return $vehicle;
        });
    }

    /**
     * Activate Vehicle
     */
    public function activateVehicle(Vehicle $vehicle, int $operatorId): Vehicle
    {
        return DB::transaction(function () use ($vehicle, $operatorId) {
            $old = $vehicle->toArray();

            $vehicle->update([
                'status' => 'available',
                'maintenance_status' => 'Good',
                'deactivated_at' => null,
                'maintenance_reason' => null,
                'breakdown_reason' => null,
            ]);

            AuditLog::create([
                'user_id' => $operatorId,
                'module' => 'Vehicle Management',
                'action' => 'Vehicle Activated',
                'table_name' => 'vehicles',
                'record_id' => $vehicle->id,
                'old_values' => json_encode($old),
                'new_values' => json_encode($vehicle->toArray()),
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System',
            ]);

            return $vehicle;
        });
    }

    /**
     * Deactivate Vehicle (Soft Deactivation - Preserves History)
     */
    public function deactivateVehicle(Vehicle $vehicle, int $operatorId): Vehicle
    {
        return DB::transaction(function () use ($vehicle, $operatorId) {
            $old = $vehicle->toArray();

            $vehicle->update([
                'status' => 'inactive',
                'deactivated_at' => now(),
            ]);

            AuditLog::create([
                'user_id' => $operatorId,
                'module' => 'Vehicle Management',
                'action' => 'Vehicle Deactivated',
                'table_name' => 'vehicles',
                'record_id' => $vehicle->id,
                'old_values' => json_encode($old),
                'new_values' => json_encode($vehicle->toArray()),
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System',
            ]);

            return $vehicle;
        });
    }

    /**
     * Mark Vehicle Under Maintenance
     */
    public function markVehicleMaintenance(Vehicle $vehicle, array $data, int $operatorId): Vehicle
    {
        if (empty(trim($data['maintenance_reason'] ?? ''))) {
            throw new InvalidArgumentException("A maintenance reason is mandatory to mark a vehicle under maintenance.");
        }

        return DB::transaction(function () use ($vehicle, $data, $operatorId) {
            $old = $vehicle->toArray();

            $vehicle->update([
                'status' => 'maintenance',
                'maintenance_status' => 'Under Scheduled Maintenance',
                'maintenance_reason' => trim($data['maintenance_reason']),
                'maintenance_start_date' => $data['maintenance_start_date'] ?? now()->format('Y-m-d'),
                'maintenance_expected_completion' => $data['maintenance_expected_completion'] ?? null,
                'maintenance_notes' => $data['maintenance_notes'] ?? null,
            ]);

            AuditLog::create([
                'user_id' => $operatorId,
                'module' => 'Vehicle Management',
                'action' => 'Vehicle Maintenance',
                'table_name' => 'vehicles',
                'record_id' => $vehicle->id,
                'old_values' => json_encode($old),
                'new_values' => json_encode($vehicle->toArray()),
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System',
            ]);

            return $vehicle;
        });
    }

    /**
     * Return Vehicle From Maintenance to Available
     */
    public function returnVehicleFromMaintenance(Vehicle $vehicle, int $operatorId): Vehicle
    {
        return DB::transaction(function () use ($vehicle, $operatorId) {
            $old = $vehicle->toArray();

            $vehicle->update([
                'status' => 'available',
                'maintenance_status' => 'Good',
                'maintenance_reason' => null,
                'maintenance_notes' => null,
            ]);

            AuditLog::create([
                'user_id' => $operatorId,
                'module' => 'Vehicle Management',
                'action' => 'Vehicle Returned From Maintenance',
                'table_name' => 'vehicles',
                'record_id' => $vehicle->id,
                'old_values' => json_encode($old),
                'new_values' => json_encode($vehicle->toArray()),
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System',
            ]);

            return $vehicle;
        });
    }

    /**
     * Mark Vehicle Breakdown
     */
    public function markVehicleBreakdown(Vehicle $vehicle, array $data, int $operatorId): Vehicle
    {
        if (empty(trim($data['breakdown_reason'] ?? ''))) {
            throw new InvalidArgumentException("A breakdown reason is mandatory to record a vehicle breakdown.");
        }

        return DB::transaction(function () use ($vehicle, $data, $operatorId) {
            $old = $vehicle->toArray();

            $vehicle->update([
                'status' => 'breakdown',
                'maintenance_status' => 'Breakdown / Out of Order',
                'breakdown_reason' => trim($data['breakdown_reason']),
                'breakdown_at' => now(),
                'breakdown_notes' => $data['breakdown_notes'] ?? null,
            ]);

            AuditLog::create([
                'user_id' => $operatorId,
                'module' => 'Vehicle Management',
                'action' => 'Vehicle Breakdown',
                'table_name' => 'vehicles',
                'record_id' => $vehicle->id,
                'old_values' => json_encode($old),
                'new_values' => json_encode($vehicle->toArray()),
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System',
            ]);

            return $vehicle;
        });
    }

    /**
     * Recover Vehicle From Breakdown to Available
     */
    public function recoverVehicleFromBreakdown(Vehicle $vehicle, int $operatorId): Vehicle
    {
        return DB::transaction(function () use ($vehicle, $operatorId) {
            $old = $vehicle->toArray();

            $vehicle->update([
                'status' => 'available',
                'maintenance_status' => 'Good',
                'breakdown_reason' => null,
                'breakdown_notes' => null,
            ]);

            AuditLog::create([
                'user_id' => $operatorId,
                'module' => 'Vehicle Management',
                'action' => 'Vehicle Recovered',
                'table_name' => 'vehicles',
                'record_id' => $vehicle->id,
                'old_values' => json_encode($old),
                'new_values' => json_encode($vehicle->toArray()),
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent() ?? 'System',
            ]);

            return $vehicle;
        });
    }

    /**
     * Maintenance & Compliance Expiry Alerts Calculator
     */
    public function getComplianceAlerts(): array
    {
        $limit30 = now()->addDays(30);

        $expiringLicenses = Driver::whereNotNull('license_expiry_date')
            ->where('license_expiry_date', '<=', $limit30)
            ->get();

        $expiringInsurance = Vehicle::whereNotNull('insurance_expiry_date')
            ->where('insurance_expiry_date', '<=', $limit30)
            ->get();

        $expiringFitness = Vehicle::whereNotNull('fitness_expiry_date')
            ->where('fitness_expiry_date', '<=', $limit30)
            ->get();

        $expiringPuc = Vehicle::whereNotNull('puc_expiry_date')
            ->where('puc_expiry_date', '<=', $limit30)
            ->get();

        $maintenanceDue = Vehicle::where(function ($q) use ($limit30) {
            $q->where('status', 'maintenance')
              ->orWhere('maintenance_status', 'Under Scheduled Maintenance')
              ->orWhere(function ($sq) use ($limit30) {
                  $sq->whereNotNull('next_service_due_date')
                     ->where('next_service_due_date', '<=', $limit30);
              });
        })->get();

        return [
            'expiring_licenses' => $expiringLicenses,
            'expiring_insurance' => $expiringInsurance,
            'expiring_fitness' => $expiringFitness,
            'expiring_puc' => $expiringPuc,
            'maintenance_due' => $maintenanceDue,
            'total_alerts' => count($expiringLicenses) + count($expiringInsurance) + count($expiringFitness) + count($expiringPuc) + count($maintenanceDue),
        ];
    }
}

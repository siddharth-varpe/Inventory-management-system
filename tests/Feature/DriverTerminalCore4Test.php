<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Driver;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DriverTerminalCore4Test extends TestCase
{
    use RefreshDatabase;

    protected Driver $driver;
    protected User $user;
    protected Vehicle $vehicle;

    protected function setUp(): void
    {
        parent::setUp();

        $this->driver = Driver::create([
            'driver_code' => 'DRV-000501',
            'driver_name' => 'Siddharth Varpe',
            'employee_id' => 'EMP-DRV-0501',
            'phone_number' => '+91 90216 53893',
            'email' => 'siddharth501@example.com',
            'license_class' => 'Heavy Commercial (HMV)',
            'driving_license_number' => 'MH-12-8888888',
            'status' => 'available',
        ]);

        $this->user = User::create([
            'name' => $this->driver->driver_name,
            'email' => $this->driver->email,
            'password' => Hash::make('password'),
            'driver_id' => $this->driver->id,
            'status' => 'active',
        ]);

        $this->vehicle = Vehicle::create([
            'vehicle_code' => 'VEH-000501',
            'vehicle_number' => 'MH12AU2233',
            'vehicle_type' => 'Heavy Commercial Vehicle',
            'manufacturer' => 'Tata Motors',
            'model' => 'Prima 2830.K',
            'fuel_type' => 'Diesel',
            'load_capacity_kg' => 7500.00,
            'volume_capacity_m3' => 22.50,
            'current_odometer_km' => 12000,
            'insurance_expiry_date' => now()->addYear(),
            'puc_expiry_date' => now()->addMonths(6),
            'permit_expiry_date' => now()->addYear(),
            'maintenance_status' => 'Good',
            'status' => 'available',
        ]);

        $this->driver->update(['current_assignment' => $this->vehicle->id]);
    }

    public function test_core4_vehicle_status_screen_renders_successfully(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('driver-terminal.vehicle.status', ['driver_code' => 'drv-000501']));

        $response->assertStatus(200);
        $response->assertViewIs('driver-terminal.vehicle.index');
        $response->assertSee('Vehicle &amp; Status', false);
        $response->assertSee('My Vehicle');
        $response->assertSee('truck3d-canvas');
        $response->assertSee('Full Vehicle Master Specifications');
        $response->assertSee('MH12AU2233');
        $response->assertSee('Vehicle Health');
        $response->assertSee('Live Status');
        $response->assertSee('Documents');
        $response->assertSee('Next Service');
        $response->assertSee('Vehicle Checklist');
        $response->assertSee('Report Issue');
        $response->assertSee('Fuel Log');
        $response->assertSee('Service History');
    }

    public function test_unassigned_driver_renders_unassigned_empty_state(): void
    {
        $unassignedDriver = Driver::create([
            'driver_code' => 'DRV-000502',
            'driver_name' => 'Unassigned Driver',
            'phone_number' => '+91 90000 00000',
            'email' => 'unassigned502@example.com',
            'status' => 'available',
        ]);

        $unassignedUser = User::create([
            'name' => $unassignedDriver->driver_name,
            'email' => $unassignedDriver->email,
            'password' => Hash::make('password'),
            'driver_id' => $unassignedDriver->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($unassignedUser)
            ->get(route('driver-terminal.vehicle.status', ['driver_code' => 'drv-000502']));

        $response->assertStatus(200);
        $response->assertSee('No vehicle assigned');
    }

    public function test_idor_protection_rejects_cross_driver_access(): void
    {
        $otherDriver = Driver::create([
            'driver_code' => 'DRV-000503',
            'driver_name' => 'Other Driver',
            'phone_number' => '+91 91111 11111',
            'email' => 'other503@example.com',
            'status' => 'available',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('driver-terminal.vehicle.status', ['driver_code' => 'drv-000503']));

        $response->assertRedirect(route('driver-terminal.index', ['driver_code' => 'drv-000501']));
    }
}

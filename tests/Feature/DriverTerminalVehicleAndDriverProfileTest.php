<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Driver;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DriverTerminalVehicleAndDriverProfileTest extends TestCase
{
    use RefreshDatabase;

    protected Driver $driver;
    protected User $user;
    protected Vehicle $vehicle;

    protected function setUp(): void
    {
        parent::setUp();

        $this->driver = Driver::create([
            'driver_code' => 'DRV-000401',
            'driver_name' => 'Siddharth Varpe',
            'employee_id' => 'EMP-DRV-0401',
            'phone_number' => '+91 90216 53893',
            'email' => 'siddharth401@example.com',
            'license_class' => 'Heavy Commercial (HMV)',
            'driving_license_number' => 'MH-12-9999999',
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
            'vehicle_code' => 'VEH-000401',
            'vehicle_number' => 'MH12AU2233',
            'vehicle_type' => 'Heavy Commercial Vehicle',
            'manufacturer' => 'Tata Motors',
            'model' => 'Prima 2830.K',
            'fuel_type' => 'Diesel',
            'load_capacity_kg' => 7500.00,
            'volume_capacity_m3' => 22.50,
            'current_odometer_km' => 12000,
            'maintenance_status' => 'Good',
            'status' => 'available',
        ]);

        $this->driver->update(['current_assignment' => $this->vehicle->id]);
    }

    public function test_vehicle_showcase_page_renders_3d_truck_and_all_vehicle_specifications(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('driver-terminal.profile', ['driver_code' => 'drv-000401']));

        $response->assertStatus(200);
        $response->assertViewIs('driver-terminal.profile.index');
        $response->assertSee('Vehicle Information');
        $response->assertSee('truck-3d.png');
        $response->assertSee('MH12AU2233');
        $response->assertSee('VEH-000401');
        $response->assertSee('Tata Motors');
        $response->assertSee('7,500.00 kg');
        $response->assertSee('Driver Profile');
    }

    public function test_dedicated_driver_profile_page_renders_credentials_and_stats(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('driver-terminal.driver-profile', ['driver_code' => 'drv-000401']));

        $response->assertStatus(200);
        $response->assertViewIs('driver-terminal.profile.driver');
        $response->assertSee('Driver Profile');
        $response->assertSee('DRIVER MASTER CREDENTIALS');
        $response->assertSee('EMP-DRV-0401');
        $response->assertSee('MH-12-9999999');
        $response->assertSee('siddharth401@example.com');
        $response->assertSee('Vehicle Page');
    }
}

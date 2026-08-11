<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Driver;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DriverTerminalCore4Test extends TestCase
{
    use RefreshDatabase;

    protected Driver $driver;
    protected User $user;

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
    }

    public function test_driver_profile_screen_renders_successfully(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('driver-terminal.driver-profile', ['driver_code' => 'drv-000501']));

        $response->assertStatus(200);
        $response->assertViewIs('driver-terminal.profile.driver');
        $response->assertSee('Driver Profile');
        $response->assertSee('Siddharth Varpe');
        $response->assertSee('DRV-000501');
    }

    public function test_legacy_profile_route_renders_driver_profile(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('driver-terminal.profile', ['driver_code' => 'drv-000501']));

        $response->assertStatus(200);
        $response->assertViewIs('driver-terminal.profile.driver');
    }
}

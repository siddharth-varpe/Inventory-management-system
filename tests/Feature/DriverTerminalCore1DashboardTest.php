<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Driver;
use App\Models\User;
use App\Models\TransportRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DriverTerminalCore1DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected Driver $driver;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->driver = Driver::create([
            'driver_code' => 'DRV-000101',
            'driver_name' => 'Siddharth Varpe',
            'phone_number' => '9876543210',
            'email' => 'siddharth@example.com',
            'license_class' => 'Heavy Commercial',
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

    public function test_driver_can_access_core1_dashboard_with_authenticated_context(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('driver-terminal.index', ['driver_code' => 'drv-000101']));

        $response->assertStatus(200);
        $response->assertViewIs('driver-terminal.home.index');
        $response->assertSee('Siddharth!');
        $response->assertSee('DRV-000101');
        $response->assertSee("Today's Overview", false);
        $response->assertSee("Today's Schedule", false);
        $response->assertSee('Trip Progress');
        $response->assertDontSee('Scan');
        $response->assertDontSee('QR Code');
    }

    public function test_generic_driver_terminal_url_redirects_to_canonical_code_url(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/driver-terminal');

        $response->assertRedirect(route('driver-terminal.index', ['driver_code' => 'drv-000101']));
    }

    public function test_driver_cannot_access_another_drivers_dashboard_url(): void
    {
        $otherDriver = Driver::create([
            'driver_code' => 'DRV-999999',
            'driver_name' => 'Other Driver',
            'phone_number' => '9999999999',
            'email' => 'other@example.com',
            'license_class' => 'LMN',
            'status' => 'available',
        ]);

        $response = $this->actingAs($this->user)
            ->get('/driver-terminal/drv-999999');

        $response->assertRedirect(route('driver-terminal.index', ['driver_code' => 'drv-000101']));
    }

    public function test_unauthenticated_guest_is_redirected_to_driver_login(): void
    {
        $response = $this->get('/driver-terminal/drv-000101');
        $response->assertRedirect(route('driver-terminal.login'));
    }
}

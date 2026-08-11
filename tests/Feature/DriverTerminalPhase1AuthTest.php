<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\TransportRequest;
use App\Models\Customer;
use App\Models\SalesOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DriverTerminalPhase1AuthTest extends TestCase
{
    use RefreshDatabase;

    protected Driver $activeDriver;
    protected Driver $inactiveDriver;
    protected Driver $otherDriver;
    protected User $driverUser;
    protected User $otherDriverUser;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create Active Driver in Driver Master
        $this->activeDriver = Driver::create([
            'driver_code' => 'DRV-000001',
            'driver_name' => 'Rajesh Kumar',
            'employee_id' => 'EMP-DRV-0001',
            'license_class' => 'Heavy Commercial (HMV)',
            'phone_number' => '+91 98765 43210',
            'email' => 'rajesh@stockmanager.com',
            'status' => 'available',
        ]);

        // Create User account linked to Active Driver
        $this->driverUser = User::factory()->create([
            'name' => 'Rajesh Kumar',
            'email' => 'rajesh@stockmanager.com',
            'password' => Hash::make('SecretPass123!'),
            'driver_id' => $this->activeDriver->id,
        ]);

        // 2. Create Inactive/Suspended Driver
        $this->inactiveDriver = Driver::create([
            'driver_code' => 'DRV-000002',
            'driver_name' => 'Suspended Driver',
            'employee_id' => 'EMP-DRV-0002',
            'license_class' => 'Light Commercial (LMV)',
            'phone_number' => '+91 91111 22222',
            'email' => 'suspended@stockmanager.com',
            'status' => 'suspended',
        ]);

        User::factory()->create([
            'name' => 'Suspended Driver',
            'email' => 'suspended@stockmanager.com',
            'password' => Hash::make('SecretPass123!'),
            'driver_id' => $this->inactiveDriver->id,
        ]);

        // 3. Create Other Driver for IDOR Tests
        $this->otherDriver = Driver::create([
            'driver_code' => 'DRV-000003',
            'driver_name' => 'Suresh Patil',
            'employee_id' => 'EMP-DRV-0003',
            'license_class' => 'Heavy Commercial (HMV)',
            'phone_number' => '+91 93333 44444',
            'email' => 'suresh@stockmanager.com',
            'status' => 'available',
        ]);

        $this->otherDriverUser = User::factory()->create([
            'name' => 'Suresh Patil',
            'email' => 'suresh@stockmanager.com',
            'password' => Hash::make('SecretPass123!'),
            'driver_id' => $this->otherDriver->id,
        ]);
    }

    /** @test */
    public function test_valid_active_driver_login_succeeds(): void
    {
        $response = $this->post(route('driver-terminal.login.post'), [
            'driver_id' => 'DRV-000001',
            'mobile_number' => '9876543210',
        ]);

        $response->assertRedirect(route('driver-terminal.index', ['driver_code' => 'drv-000001']));
        $this->assertAuthenticatedAs($this->driverUser);
    }

    /** @test */
    public function test_invalid_credentials_are_rejected(): void
    {
        $response = $this->post(route('driver-terminal.login.post'), [
            'driver_id' => 'DRV-000001',
            'mobile_number' => '0000000000',
        ]);

        $response->assertSessionHasErrors(['driver_id']);
        $this->assertGuest();
    }

    /** @test */
    public function test_inactive_or_suspended_driver_login_is_rejected(): void
    {
        $response = $this->post(route('driver-terminal.login.post'), [
            'driver_id' => 'DRV-000002',
            'mobile_number' => '9111122222',
        ]);

        $response->assertSessionHasErrors(['driver_id']);
        $this->assertGuest();
    }

    /** @test */
    public function test_authenticated_driver_identity_is_maintained_server_side(): void
    {
        $this->actingAs($this->driverUser);

        $response = $this->get(route('driver-terminal.index', ['driver_code' => 'drv-000001']));
        $response->assertOk();
        $response->assertSee('DRV-000001');
        $response->assertSee('Rajesh Kumar');
    }

    /** @test */
    public function test_logout_invalidates_session_and_redirects_to_login(): void
    {
        $this->actingAs($this->driverUser);

        $response = $this->post(route('driver-terminal.logout'));
        $response->assertRedirect(route('driver-terminal.login'));
        $this->assertGuest();

        // Protected terminal route redirects back to login after logout
        $protectedRes = $this->get('/driver-terminal/drv-000001');
        $protectedRes->assertRedirect(route('driver-terminal.login'));
    }

    /** @test */
    public function test_unauthenticated_access_to_protected_driver_terminal_is_rejected(): void
    {
        $response = $this->get('/driver-terminal/drv-000001');
        $response->assertRedirect(route('driver-terminal.login'));
    }

    /** @test */
    public function test_idor_protection_prevents_driver_from_accessing_other_drivers_terminal(): void
    {
        $unlinkedUser = User::factory()->create(['driver_id' => null]);
        $this->actingAs($unlinkedUser);
        $response = $this->get('/driver-terminal/drv-000001');

        $response->assertRedirect('/driver-terminal/login');
    }
}

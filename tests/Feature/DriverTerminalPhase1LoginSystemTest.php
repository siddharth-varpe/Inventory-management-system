<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Driver;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DriverTerminalPhase1LoginSystemTest extends TestCase
{
    use RefreshDatabase;

    private Driver $driverA;
    private Driver $driverB;
    private Driver $inactiveDriver;
    private User $userA;
    private User $userB;
    private User $userInactive;
    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create Driver Master A (DRV-000001)
        $this->driverA = Driver::create([
            'driver_code' => 'DRV-000001',
            'driver_name' => 'Siddharth Varpe',
            'employee_id' => 'EMP-DRV-0001',
            'email' => 'varpes380@gmail.com',
            'phone_number' => '+91 90216 53893',
            'status' => 'available',
        ]);

        // 2. Create Driver Master B (DRV-000002)
        $this->driverB = Driver::create([
            'driver_code' => 'DRV-000002',
            'driver_name' => 'Second Driver',
            'employee_id' => 'EMP-DRV-0002',
            'email' => 'driver2@stockmanager.com',
            'phone_number' => '+91 89887 67543',
            'status' => 'available',
        ]);

        // 3. Create Inactive Driver Master (DRV-000003)
        $this->inactiveDriver = Driver::create([
            'driver_code' => 'DRV-000003',
            'driver_name' => 'Inactive Driver',
            'employee_id' => 'EMP-DRV-0003',
            'email' => 'inactive@stockmanager.com',
            'phone_number' => '+91 99999 88888',
            'status' => 'suspended',
        ]);

        // 4. Create User Accounts linked via driver_id
        $this->userA = User::create([
            'name' => 'Siddharth Varpe',
            'email' => 'varpes380@gmail.com',
            'password' => bcrypt('password123'),
            'driver_id' => $this->driverA->id,
            'status' => 'active',
        ]);

        $this->userB = User::create([
            'name' => 'Second Driver',
            'email' => 'driver2@stockmanager.com',
            'password' => bcrypt('password123'),
            'driver_id' => $this->driverB->id,
            'status' => 'active',
        ]);

        $this->userInactive = User::create([
            'name' => 'Inactive Driver',
            'email' => 'inactive@stockmanager.com',
            'password' => bcrypt('password123'),
            'driver_id' => $this->inactiveDriver->id,
            'status' => 'active',
        ]);

        // 5. Unlinked Enterprise Administrator
        $this->adminUser = User::create([
            'name' => 'Enterprise Administrator',
            'email' => 'admin@stockmanager.com',
            'password' => bcrypt('admin123'),
            'driver_id' => null,
            'status' => 'active',
        ]);
    }

    /** @test — Driver A ID + Driver A Mobile -> SUCCESS */
    public function driver_a_login_with_matching_registered_mobile_succeeds(): void
    {
        $response = $this->post('/driver-terminal/login', [
            'driver_id' => 'DRV-000001',
            'mobile_number' => '9021653893',
        ]);

        $response->assertRedirect('/driver-terminal');
        $this->assertAuthenticatedAs($this->userA);
    }

    /** @test — Driver B ID + Driver B Mobile -> SUCCESS */
    public function driver_b_login_with_matching_registered_mobile_succeeds(): void
    {
        $response = $this->post('/driver-terminal/login', [
            'driver_id' => 'DRV-000002',
            'mobile_number' => '8988767543',
        ]);

        $response->assertRedirect('/driver-terminal');
        $this->assertAuthenticatedAs($this->userB);
    }

    /** @test — Cross-Driver Test: Driver A ID + Driver B Mobile -> REJECTED */
    public function driver_a_id_with_driver_b_mobile_is_rejected(): void
    {
        $response = $this->post('/driver-terminal/login', [
            'driver_id' => 'DRV-000001',
            'mobile_number' => '8988767543',
        ]);

        $response->assertSessionHasErrors('driver_id');
        $this->assertGuest();
    }

    /** @test — Cross-Driver Test: Driver B ID + Driver A Mobile -> REJECTED */
    public function driver_b_id_with_driver_a_mobile_is_rejected(): void
    {
        $response = $this->post('/driver-terminal/login', [
            'driver_id' => 'DRV-000002',
            'mobile_number' => '9021653893',
        ]);

        $response->assertSessionHasErrors('driver_id');
        $this->assertGuest();
    }

    /** @test — Invalid Driver ID + Valid Mobile -> REJECTED */
    public function invalid_driver_id_is_rejected(): void
    {
        $response = $this->post('/driver-terminal/login', [
            'driver_id' => 'DRV-999999',
            'mobile_number' => '9021653893',
        ]);

        $response->assertSessionHasErrors('driver_id');
        $this->assertGuest();
    }

    /** @test — Valid Driver ID + Invalid Mobile -> REJECTED */
    public function valid_driver_id_with_wrong_mobile_is_rejected(): void
    {
        $response = $this->post('/driver-terminal/login', [
            'driver_id' => 'DRV-000001',
            'mobile_number' => '9999988888',
        ]);

        $response->assertSessionHasErrors('driver_id');
        $this->assertGuest();
    }

    /** @test — Inactive Driver -> REJECTED */
    public function inactive_driver_login_is_rejected(): void
    {
        $response = $this->post('/driver-terminal/login', [
            'driver_id' => 'DRV-000003',
            'mobile_number' => '9999988888',
        ]);

        $response->assertSessionHasErrors('driver_id');
        $this->assertGuest();
    }

    /** @test — Authenticated Driver identity resolved on temporary screen */
    public function authenticated_driver_identity_is_resolved_on_temporary_screen(): void
    {
        $this->actingAs($this->userA);

        $response = $this->get('/driver-terminal');
        $response->assertStatus(200);
        $response->assertSee('DRIVER TERMINAL');
        $response->assertSee('DRV-000001');
        $response->assertSee('Siddharth Varpe');
        $response->assertSee('Driver login successful.');
    }

    /** @test — Logout invalidates session */
    public function logout_invalidates_session_and_redirects_to_login(): void
    {
        $this->actingAs($this->userA);

        $response = $this->post('/driver-terminal/logout');

        $response->assertRedirect('/driver-terminal/login');
        $this->assertGuest();
    }

    /** @test — Access temporary authenticated page after logout redirects to login */
    public function accessing_driver_terminal_after_logout_redirects_to_login(): void
    {
        $response = $this->get('/driver-terminal');

        $response->assertRedirect('/driver-terminal/login');
    }

    /** @test — Administrator attempts Driver Terminal */
    public function unlinked_administrator_account_redirects_to_login(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->get('/driver-terminal');

        $response->assertRedirect('/driver-terminal/login');
    }
}

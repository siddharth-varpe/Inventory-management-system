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
        ]);
    }

    /** @test */
    public function test_valid_active_driver_login_succeeds(): void
    {
        $response = $this->post(route('driver-terminal.login.post'), [
            'driver_identifier' => 'DRV-000001',
            'password' => 'SecretPass123!',
        ]);

        $response->assertRedirect(route('driver-terminal.index'));
        $this->assertAuthenticatedAs($this->driverUser);
    }

    /** @test */
    public function test_driver_login_with_email_succeeds(): void
    {
        $response = $this->post(route('driver-terminal.login.post'), [
            'driver_identifier' => 'rajesh@stockmanager.com',
            'password' => 'SecretPass123!',
        ]);

        $response->assertRedirect(route('driver-terminal.index'));
        $this->assertAuthenticatedAs($this->driverUser);
    }

    /** @test */
    public function test_invalid_credentials_are_rejected(): void
    {
        $response = $this->post(route('driver-terminal.login.post'), [
            'driver_identifier' => 'DRV-000001',
            'password' => 'WrongPassword!',
        ]);

        $response->assertSessionHasErrors(['driver_identifier']);
        $this->assertGuest();
    }

    /** @test */
    public function test_inactive_or_suspended_driver_login_is_rejected(): void
    {
        $response = $this->post(route('driver-terminal.login.post'), [
            'driver_identifier' => 'DRV-000002',
            'password' => 'SecretPass123!',
        ]);

        $response->assertSessionHasErrors(['driver_identifier']);
        $this->assertGuest();
    }

    /** @test */
    public function test_authenticated_driver_identity_is_maintained_server_side(): void
    {
        $this->actingAs($this->driverUser);

        $response = $this->get(route('driver-terminal.index'));
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
        $protectedRes = $this->get(route('driver-terminal.index'));
        $protectedRes->assertRedirect(route('driver-terminal.login'));
    }

    /** @test */
    public function test_unauthenticated_access_to_protected_driver_terminal_is_rejected(): void
    {
        $response = $this->get(route('driver-terminal.index'));
        $response->assertRedirect(route('driver-terminal.login'));
    }

    /** @test */
    public function test_idor_protection_prevents_driver_from_accessing_other_drivers_delivery(): void
    {
        // Seed Customer & Sales Order
        $customer = Customer::create([
            'customer_code' => 'CUST-001',
            'company_name' => 'Acme Corp',
            'contact_person' => 'John Doe',
            'email' => 'john@acme.com',
            'phone' => '9888877777',
            'status' => 'active',
        ]);

        $salesOrder = SalesOrder::create([
            'order_number' => 'SO-2026-00001',
            'order_date' => now()->toDateString(),
            'customer_id' => $customer->id,
            'total_amount' => 5000.00,
            'status' => 'approved',
        ]);

        // Seed Delivery task assigned ONLY to Other Driver (DRV-000003)
        $deliveryOtherDriver = TransportRequest::create([
            'request_number' => 'TRN-2026-00099',
            'sales_order_id' => $salesOrder->id,
            'driver_id' => $this->otherDriver->id,
            'order_reference' => 'SO-2026-00001',
            'customer_name' => 'Acme Corp',
            'delivery_address' => '123 Main St, Mumbai',
            'delivery_city' => 'Mumbai',
            'contact_person' => 'John Doe',
            'phone_number' => '9888877777',
            'weight_kg' => 50.0,
            'volume_m3' => 0.5,
            'status' => 'driver_vehicle_assigned',
        ]);

        // Attempt to access driver terminal route as unlinked user
        $unlinkedUser = User::factory()->create(['driver_id' => null]);
        $this->actingAs($unlinkedUser);
        $response = $this->get(route('driver-terminal.index'));

        // MUST redirect to login with error
        $response->assertRedirect('/driver-terminal/login');
    }
}

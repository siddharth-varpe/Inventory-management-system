<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Driver;
use App\Models\SalesOrder;
use App\Models\TransportRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DriverTerminalSecurityMatrixTest extends TestCase
{
    use RefreshDatabase;

    private Driver $driverA;
    private Driver $driverB;
    private User $userA;
    private User $userB;
    private Customer $customer;
    private SalesOrder $salesOrderA;
    private SalesOrder $salesOrderB;
    private TransportRequest $deliveryA;
    private TransportRequest $deliveryB;

    protected function setUp(): void
    {
        parent::setUp();

        // Create Customer
        $this->customer = Customer::create([
            'customer_code' => 'CUST-000001',
            'company_name' => 'Customer Alpha',
            'email' => 'customer@example.com',
            'phone' => '+919000000000',
            'status' => 'active',
        ]);

        // Create Driver Master A (DRV-000001)
        $this->driverA = Driver::create([
            'driver_code' => 'DRV-000001',
            'driver_name' => 'Driver Alpha',
            'employee_id' => 'EMP-DRV-0001',
            'email' => 'driver.alpha@stockmanager.com',
            'phone_number' => '+91 98765 43210',
            'status' => 'available',
        ]);

        // Create Driver Master B (DRV-000002)
        $this->driverB = Driver::create([
            'driver_code' => 'DRV-000002',
            'driver_name' => 'Driver Beta',
            'employee_id' => 'EMP-DRV-0002',
            'email' => 'driver.beta@stockmanager.com',
            'phone_number' => '+91 91234 56789',
            'status' => 'available',
        ]);

        // Create User Accounts
        $this->userA = User::create([
            'name' => 'Driver Alpha',
            'email' => 'driver.alpha@stockmanager.com',
            'password' => bcrypt('password123'),
            'driver_id' => $this->driverA->id,
            'status' => 'active',
        ]);

        $this->userB = User::create([
            'name' => 'Driver Beta',
            'email' => 'driver.beta@stockmanager.com',
            'password' => bcrypt('password123'),
            'driver_id' => $this->driverB->id,
            'status' => 'active',
        ]);

        // Create Sales Orders
        $this->salesOrderA = SalesOrder::create([
            'order_number' => 'SO-2026-00001',
            'customer_id' => $this->customer->id,
            'salesperson_id' => $this->userA->id,
            'created_by' => $this->userA->id,
            'order_date' => now(),
            'status' => 'confirmed',
            'grand_total' => 1000.00,
        ]);

        $this->salesOrderB = SalesOrder::create([
            'order_number' => 'SO-2026-00002',
            'customer_id' => $this->customer->id,
            'salesperson_id' => $this->userB->id,
            'created_by' => $this->userB->id,
            'order_date' => now(),
            'status' => 'confirmed',
            'grand_total' => 2000.00,
        ]);

        // Create Deliveries
        $this->deliveryA = TransportRequest::create([
            'request_number' => 'TRN-REQ-000001',
            'order_reference' => 'SO-2026-00001',
            'sales_order_id' => $this->salesOrderA->id,
            'driver_id' => $this->driverA->id,
            'customer_name' => 'Customer Alpha',
            'delivery_address' => '123 Alpha Way',
            'status' => 'driver_vehicle_assigned',
        ]);

        $this->deliveryB = TransportRequest::create([
            'request_number' => 'TRN-REQ-000002',
            'order_reference' => 'SO-2026-00002',
            'sales_order_id' => $this->salesOrderB->id,
            'driver_id' => $this->driverB->id,
            'customer_name' => 'Customer Beta',
            'delivery_address' => '456 Beta Ave',
            'status' => 'driver_vehicle_assigned',
        ]);
    }

    /** @test — Matrix Row 1: Logged out + own URL -> Redirect to login */
    public function logged_out_user_accessing_own_url_redirects_to_login(): void
    {
        $response = $this->get('/driver-terminal/drv-000001');
        $response->assertRedirect('/driver-terminal/login');
    }

    /** @test — Matrix Row 2: Logged out + other URL -> Redirect to login */
    public function logged_out_user_accessing_other_url_redirects_to_login(): void
    {
        $response = $this->get('/driver-terminal/drv-000002');
        $response->assertRedirect('/driver-terminal/login');
    }

    /** @test — Matrix Row 3: Valid login -> Redirect to own driver URL */
    public function valid_login_redirects_to_own_driver_scoped_url(): void
    {
        $response = $this->post('/driver-terminal/login', [
            'driver_id' => 'DRV-000001',
            'mobile_number' => '9876543210',
        ]);

        $response->assertRedirect('/driver-terminal/drv-000001');
        $this->assertAuthenticatedAs($this->userA);
    }

    /** @test — Matrix Row 4: Invalid credentials -> Rejected */
    public function invalid_credentials_are_rejected(): void
    {
        $response = $this->post('/driver-terminal/login', [
            'driver_id' => 'DRV-000001',
            'mobile_number' => '0000000000',
        ]);

        $response->assertSessionHasErrors('driver_id');
        $this->assertGuest();
    }

    /** @test — Matrix Row 5: Driver A + Driver A URL -> Allowed */
    public function driver_a_accessing_driver_a_url_is_allowed(): void
    {
        $this->actingAs($this->userA);

        $response = $this->get('/driver-terminal/drv-000001');
        $response->assertStatus(200);
        $response->assertSee('Driver Alpha');
    }

    /** @test — Matrix Row 6: Driver A + Driver B URL -> Denied (Redirected to own URL) */
    public function driver_a_accessing_driver_b_url_is_denied(): void
    {
        $this->actingAs($this->userA);

        $response = $this->get('/driver-terminal/drv-000002');
        $response->assertRedirect('/driver-terminal/drv-000001');
        $response->assertSessionHas('error', 'Access denied. This Driver Terminal belongs to another driver.');
    }

    /** @test — Matrix Row 7: Driver B + Driver A URL -> Denied (Redirected to own URL) */
    public function driver_b_accessing_driver_a_url_is_denied(): void
    {
        $this->actingAs($this->userB);

        $response = $this->get('/driver-terminal/drv-000001');
        $response->assertRedirect('/driver-terminal/drv-000002');
        $response->assertSessionHas('error', 'Access denied. This Driver Terminal belongs to another driver.');
    }

    /** @test — Matrix Row 8: Logout + own URL -> Redirect to login */
    public function logout_then_accessing_own_url_redirects_to_login(): void
    {
        $this->actingAs($this->userA);
        $this->post('/driver-terminal/logout');

        $response = $this->get('/driver-terminal/drv-000001');
        $response->assertRedirect('/driver-terminal/login');
        $this->assertGuest();
    }

    /** @test — Matrix Row 9: Expired session + own URL -> Redirect to login */
    public function expired_session_accessing_own_url_redirects_to_login(): void
    {
        // Unauthenticated request simulates expired session
        $response = $this->get('/driver-terminal/drv-000001');
        $response->assertRedirect('/driver-terminal/login');
    }

    /** @test — Matrix Row 10: Driver A delivery request -> Only A's deliveries visible */
    public function driver_a_delivery_queue_only_shows_driver_a_deliveries(): void
    {
        $this->actingAs($this->userA);

        $response = $this->get('/driver-terminal/drv-000001/deliveries');
        $response->assertStatus(200);
        $response->assertSee('TRN-REQ-000001');
        $response->assertDontSee('TRN-REQ-000002');
    }

    /** @test — Matrix Row 11: Driver B delivery request -> Only B's deliveries visible */
    public function driver_b_delivery_queue_only_shows_driver_b_deliveries(): void
    {
        $this->actingAs($this->userB);

        $response = $this->get('/driver-terminal/drv-000002/deliveries');
        $response->assertStatus(200);
        $response->assertSee('TRN-REQ-000002');
        $response->assertDontSee('TRN-REQ-000001');
    }

    /** @test — Delivery IDOR Protection: Driver A accessing Driver B delivery detail is denied */
    public function driver_a_accessing_driver_b_delivery_detail_is_denied(): void
    {
        $this->actingAs($this->userA);

        $response = $this->get('/driver-terminal/drv-000001/deliveries/' . $this->deliveryB->id);
        $response->assertRedirect('/driver-terminal/drv-000001');
        $response->assertSessionHas('error', 'Access denied. You do not have permission to view or manage this delivery.');
    }

    /** @test — Already authenticated driver visiting login page redirects to own terminal */
    public function authenticated_driver_visiting_login_redirects_to_own_terminal(): void
    {
        $this->actingAs($this->userA);

        $response = $this->get('/driver-terminal/login');
        $response->assertRedirect('/driver-terminal/drv-000001');
    }
}

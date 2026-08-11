<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Driver;
use App\Models\SalesOrder;
use App\Models\TransportRequest;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DriverTerminalPhase3AcceptanceTest extends TestCase
{
    use RefreshDatabase;

    private Driver $driverA;
    private Driver $driverB;
    private User $userA;
    private User $userB;
    private User $adminUser;
    private Vehicle $vehicle;
    private Customer $customer;
    private SalesOrder $salesOrder;
    private TransportRequest $deliveryA;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create Vehicles & Customers
        $this->vehicle = Vehicle::create([
            'vehicle_number' => 'MH12AU2233',
            'vehicle_type' => 'Van',
            'status' => 'available',
        ]);

        $this->customer = Customer::create([
            'customer_code' => 'CUST-000001',
            'company_name' => 'opstech solution',
            'email' => 'opstech@example.com',
            'phone' => '+919000000000',
            'status' => 'active',
        ]);

        // 2. Create Driver Masters
        $this->driverA = Driver::create([
            'driver_code' => 'DRV-000001',
            'driver_name' => 'Siddharth Varpe',
            'employee_id' => 'EMP-DRV-0001',
            'email' => 'varpes380@gmail.com',
            'phone_number' => '+919021653893',
            'status' => 'available',
        ]);

        $this->driverB = Driver::create([
            'driver_code' => 'DRV-000002',
            'driver_name' => 'Second Driver',
            'employee_id' => 'EMP-DRV-0002',
            'email' => 'driver2@stockmanager.com',
            'phone_number' => '+918988767543',
            'status' => 'available',
        ]);

        // 3. Create Users with driver_id foreign key links
        $this->userA = User::create([
            'name' => 'Siddharth Varpe',
            'email' => 'varpes380@gmail.com',
            'password' => bcrypt('password'),
            'driver_id' => $this->driverA->id,
            'status' => 'active',
        ]);

        $this->userB = User::create([
            'name' => 'Second Driver',
            'email' => 'driver2@stockmanager.com',
            'password' => bcrypt('password'),
            'driver_id' => $this->driverB->id,
            'status' => 'active',
        ]);

        $this->adminUser = User::create([
            'name' => 'Enterprise Administrator',
            'email' => 'admin@stockmanager.com',
            'password' => bcrypt('password'),
            'driver_id' => null,
            'status' => 'active',
        ]);

        $this->salesOrder = SalesOrder::create([
            'order_number' => 'SO-2026-00001',
            'customer_id' => $this->customer->id,
            'salesperson_id' => $this->userA->id,
            'created_by' => $this->userA->id,
            'order_date' => now(),
            'status' => 'confirmed',
            'grand_total' => 1000.00,
        ]);

        // 4. Create Assigned Delivery for Driver A
        $this->deliveryA = TransportRequest::create([
            'request_number' => 'TRN-2026-00001',
            'sales_order_id' => $this->salesOrder->id,
            'order_reference' => 'SO-2026-00001',
            'customer_name' => 'opstech solution',
            'delivery_address' => 'Primary Customer Address, Pune',
            'priority' => 'high',
            'status' => 'driver_vehicle_assigned',
            'driver_id' => $this->driverA->id,
            'assigned_driver_id' => $this->driverA->id,
            'created_by' => $this->userA->id,
            'vehicle_id' => $this->vehicle->id,
        ]);
    }

    /** @test */
    public function authenticated_driver_can_view_assigned_deliveries_list(): void
    {
        $response = $this->actingAs($this->userA)->get('/driver-terminal/deliveries');

        $response->assertStatus(200);
        $response->assertSee('My Deliveries');
        $response->assertSee('SO-2026-00001');
        $response->assertSee('opstech solution');
        $response->assertSee('ASSIGNED');
    }

    /** @test */
    public function driver_search_filters_deliveries_by_order_reference(): void
    {
        $response = $this->actingAs($this->userA)->get('/driver-terminal/deliveries?search=SO-2026-00001');

        $response->assertStatus(200);
        $response->assertSee('SO-2026-00001');
    }

    /** @test */
    public function authenticated_driver_can_view_delivery_details(): void
    {
        $response = $this->actingAs($this->userA)->get("/driver-terminal/deliveries/{$this->deliveryA->id}");

        $response->assertStatus(200);
        $response->assertSee('SO-2026-00001');
        $response->assertSee('opstech solution');
        $response->assertSee('MH12AU2233');
        $response->assertSee('ACCEPT DELIVERY');
    }

    /** @test */
    public function driver_can_accept_assigned_delivery_updating_status_to_dispatched(): void
    {
        $response = $this->actingAs($this->userA)
            ->postJson("/driver-terminal/deliveries/{$this->deliveryA->id}/accept");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'status' => 'dispatched',
        ]);

        $this->assertDatabaseHas('transport_requests', [
            'id' => $this->deliveryA->id,
            'status' => 'dispatched',
        ]);

        $this->assertNotNull($this->deliveryA->fresh()->dispatched_at);
        $this->assertNotNull($this->deliveryA->fresh()->accepted_at);
    }

    /** @test */
    public function double_acceptance_attempt_is_prevented_with_clean_error(): void
    {
        // First acceptance
        $this->actingAs($this->userA)->postJson("/driver-terminal/deliveries/{$this->deliveryA->id}/accept");

        // Second acceptance attempt
        $response = $this->actingAs($this->userA)
            ->postJson("/driver-terminal/deliveries/{$this->deliveryA->id}/accept");

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'already_dispatched' => true,
        ]);
    }

    /** @test */
    public function driver_b_cannot_view_or_accept_driver_a_delivery(): void
    {
        // Driver B attempts to view Driver A's delivery
        $viewResponse = $this->actingAs($this->userB)->get("/driver-terminal/deliveries/{$this->deliveryA->id}");
        $viewResponse->assertStatus(403);

        // Driver B attempts to accept Driver A's delivery
        $acceptResponse = $this->actingAs($this->userB)
            ->postJson("/driver-terminal/deliveries/{$this->deliveryA->id}/accept");
        $acceptResponse->assertStatus(403);
    }

    /** @test */
    public function driver_with_no_assigned_deliveries_sees_clean_empty_state(): void
    {
        $response = $this->actingAs($this->userB)->get('/driver-terminal/deliveries');

        $response->assertStatus(200);
        $response->assertSee('NO ASSIGNED DELIVERIES');
        $response->assertSee('You currently have no deliveries assigned to you.');
    }

    /** @test */
    public function unlinked_administrator_account_receives_403_forbidden(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/driver-terminal/deliveries');

        $response->assertStatus(403);
    }
}

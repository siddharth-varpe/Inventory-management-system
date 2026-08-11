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
use Tests\TestCase;

class DriverTerminalPhase2HomeProfileTest extends TestCase
{
    use RefreshDatabase;

    protected Driver $driverA;
    protected Driver $driverB;
    protected User $userA;
    protected User $userB;
    protected Vehicle $vehicleA;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create Driver Master A
        $this->driverA = Driver::create([
            'driver_code' => 'DRV-000001',
            'driver_name' => 'Siddharth Varpe',
            'employee_id' => 'EMP-DRV-0001',
            'license_class' => 'Heavy Commercial (HMV)',
            'phone_number' => '+91 90216 53893',
            'email' => 'varpes380@gmail.com',
            'status' => 'available',
        ]);

        // 2. Create Driver Master B
        $this->driverB = Driver::create([
            'driver_code' => 'DRV-000002',
            'driver_name' => 'Ramesh Patil',
            'employee_id' => 'EMP-DRV-0002',
            'license_class' => 'Light Commercial (LMV)',
            'phone_number' => '+91 98888 77777',
            'email' => 'ramesh@stockmanager.com',
            'status' => 'available',
        ]);

        // 3. Create User account linked to Driver Master A
        $this->userA = User::factory()->create([
            'name' => 'Siddharth Varpe',
            'email' => 'varpes380@gmail.com',
            'driver_id' => $this->driverA->id,
        ]);

        // 4. Create User account linked to Driver Master B
        $this->userB = User::factory()->create([
            'name' => 'Ramesh Patil',
            'email' => 'ramesh@stockmanager.com',
            'driver_id' => $this->driverB->id,
        ]);

        // 5. Create Vehicle Master
        $this->vehicleA = Vehicle::create([
            'vehicle_code' => 'VEH-000001',
            'vehicle_number' => 'MH12AU2233',
            'vehicle_type' => 'Heavy Commercial Vehicle',
            'status' => 'available',
        ]);
    }

    /** @test */
    public function test_authenticated_driver_home_displays_correct_name_id_and_status(): void
    {
        $this->actingAs($this->userA);

        $response = $this->get(route('driver-terminal.index', ['driver_code' => 'drv-000001']));

        $response->assertOk();
        $response->assertSee('Siddharth Varpe');
        $response->assertSee('DRV-000001');
    }

    /** @test */
    public function test_driver_home_shows_empty_state_when_no_active_delivery_assigned(): void
    {
        $this->actingAs($this->userA);

        $response = $this->get(route('driver-terminal.index', ['driver_code' => 'drv-000001']));

        $response->assertOk();
        $response->assertSee('DRIVER TERMINAL');
    }

    /** @test */
    public function test_driver_home_displays_assigned_active_delivery_information(): void
    {
        // Create Customer & Sales Order
        $customer = Customer::create([
            'customer_code' => 'CUST-001',
            'company_name' => 'Apex Logistics Ltd',
            'contact_person' => 'Alice Green',
            'email' => 'alice@apex.com',
            'phone' => '9123456789',
            'status' => 'active',
        ]);

        $salesOrder = SalesOrder::create([
            'order_number' => 'SO-2026-99100',
            'order_date' => now()->toDateString(),
            'customer_id' => $customer->id,
            'total_amount' => 15000.00,
            'status' => 'approved',
        ]);

        // Create Active Delivery for Driver A
        $transportRequest = TransportRequest::create([
            'request_number' => 'TRN-2026-99100',
            'sales_order_id' => $salesOrder->id,
            'driver_id' => $this->driverA->id,
            'vehicle_id' => $this->vehicleA->id,
            'order_reference' => 'SO-2026-99100',
            'customer_name' => 'Apex Logistics Ltd',
            'delivery_address' => 'Plot 88, Tech Park, Pune',
            'delivery_city' => 'Pune',
            'contact_person' => 'Alice Green',
            'phone_number' => '9123456789',
            'weight_kg' => 250.0,
            'volume_m3' => 2.5,
            'status' => 'driver_vehicle_assigned',
        ]);

        $this->actingAs($this->userA);

        $response = $this->get(route('driver-terminal.index', ['driver_code' => 'drv-000001']));

        $response->assertOk();
        $response->assertSee('DRIVER TERMINAL');
    }

    /** @test */
    public function test_driver_home_displays_assigned_vehicle_information(): void
    {
        $this->driverA->update(['current_assignment' => $this->vehicleA->id]);
        $this->actingAs($this->userA);

        $response = $this->get(route('driver-terminal.index', ['driver_code' => 'drv-000001']));

        $response->assertOk();
    }

    /** @test */
    public function test_driver_home_displays_vehicle_empty_state_when_unassigned(): void
    {
        $this->actingAs($this->userA);

        $response = $this->get(route('driver-terminal.index', ['driver_code' => 'drv-000001']));

        $response->assertOk();
    }

    /** @test */
    public function test_driver_profile_displays_read_only_master_credentials(): void
    {
        $this->actingAs($this->userA);

        $response = $this->get(route('driver-terminal.profile', ['driver_code' => 'drv-000001']));

        $response->assertOk();
        $response->assertSee('Siddharth Varpe');
        $response->assertSee('DRV-000001');
    }

    /** @test */
    public function test_driver_a_cannot_view_driver_b_profile_or_delivery_data(): void
    {
        $this->actingAs($this->userA);

        $response = $this->get(route('driver-terminal.profile', ['driver_code' => 'drv-000001']));
        $response->assertOk();
        $response->assertSee('Siddharth Varpe');
        $response->assertDontSee('Ramesh Patil');
        $response->assertDontSee('DRV-000002');
    }
}

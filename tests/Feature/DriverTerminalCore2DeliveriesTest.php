<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Driver;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\TransportRequest;
use App\Models\Customer;
use App\Models\SalesOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DriverTerminalCore2DeliveriesTest extends TestCase
{
    use RefreshDatabase;

    protected Driver $driverA;
    protected Driver $driverB;
    protected User $userA;
    protected User $userB;
    protected Vehicle $vehicle;

    protected function setUp(): void
    {
        parent::setUp();

        $this->driverA = Driver::create([
            'driver_code' => 'DRV-000201',
            'driver_name' => 'Siddharth Varpe',
            'phone_number' => '9876543210',
            'email' => 'siddharth201@example.com',
            'license_class' => 'Heavy Commercial',
            'status' => 'available',
        ]);

        $this->driverB = Driver::create([
            'driver_code' => 'DRV-000202',
            'driver_name' => 'Ramesh Patil',
            'phone_number' => '9888877777',
            'email' => 'ramesh202@example.com',
            'license_class' => 'Light Commercial',
            'status' => 'available',
        ]);

        $this->userA = User::create([
            'name' => $this->driverA->driver_name,
            'email' => $this->driverA->email,
            'password' => Hash::make('password'),
            'driver_id' => $this->driverA->id,
            'status' => 'active',
        ]);

        $this->userB = User::create([
            'name' => $this->driverB->driver_name,
            'email' => $this->driverB->email,
            'password' => Hash::make('password'),
            'driver_id' => $this->driverB->id,
            'status' => 'active',
        ]);

        $this->vehicle = Vehicle::create([
            'vehicle_code' => 'VEH-000201',
            'vehicle_number' => 'MH12AU9999',
            'vehicle_type' => 'Van',
            'status' => 'available',
        ]);

        $customer = Customer::create([
            'customer_code' => 'CUST-201',
            'company_name' => 'Tech Solutions Ltd',
            'contact_person' => 'Bob Smith',
            'email' => 'bob@tech.com',
            'phone' => '9876500000',
            'status' => 'active',
        ]);

        $salesOrder = SalesOrder::create([
            'order_number' => 'SO-2026-99201',
            'order_date' => now()->toDateString(),
            'customer_id' => $customer->id,
            'total_amount' => 50000.00,
            'status' => 'approved',
        ]);

        // Transport request assigned to Driver A
        TransportRequest::create([
            'request_number' => 'TRN-2026-99201',
            'sales_order_id' => $salesOrder->id,
            'driver_id' => $this->driverA->id,
            'vehicle_id' => $this->vehicle->id,
            'order_reference' => 'SO-2026-99201',
            'customer_name' => 'Tech Solutions Ltd',
            'delivery_address' => 'Baner Road, Pune',
            'delivery_city' => 'Pune',
            'status' => 'dispatched',
            'expected_delivery_date' => now()->toDateString(),
        ]);
    }

    public function test_driver_can_access_deliveries_queue_page(): void
    {
        $response = $this->actingAs($this->userA)
            ->get(route('driver-terminal.deliveries.index', ['driver_code' => 'drv-000201']));

        $response->assertStatus(200);
        $response->assertViewIs('driver-terminal.deliveries.index');
        $response->assertSee('My Deliveries');
        $response->assertSee('SO-2026-99201');
        $response->assertSee('Tech Solutions Ltd');
        $response->assertSee('Ongoing');
        $response->assertDontSee('Scan');
    }

    public function test_deliveries_queue_is_driver_scoped_and_does_not_leak_other_drivers_deliveries(): void
    {
        // Driver B visits their own queue and should NOT see Driver A's shipment
        $response = $this->actingAs($this->userB)
            ->get(route('driver-terminal.deliveries.index', ['driver_code' => 'drv-000202']));

        $response->assertStatus(200);
        $response->assertDontSee('SO-2026-99201');
        $response->assertSee('No deliveries found', false);
    }

    public function test_driver_cannot_access_another_drivers_deliveries_url(): void
    {
        $response = $this->actingAs($this->userA)
            ->get('/driver-terminal/drv-000202/deliveries');

        $response->assertRedirect(route('driver-terminal.index', ['driver_code' => 'drv-000201']));
    }

    public function test_status_tab_filtering_works_correctly(): void
    {
        // Ongoing tab
        $response = $this->actingAs($this->userA)
            ->get(route('driver-terminal.deliveries.index', ['driver_code' => 'drv-000201', 'tab' => 'ongoing']));

        $response->assertStatus(200);
        $response->assertSee('SO-2026-99201');

        // Completed tab (should be empty for Driver A)
        $responseCompleted = $this->actingAs($this->userA)
            ->get(route('driver-terminal.deliveries.index', ['driver_code' => 'drv-000201', 'tab' => 'completed']));

        $responseCompleted->assertStatus(200);
        $responseCompleted->assertDontSee('SO-2026-99201');
    }
}

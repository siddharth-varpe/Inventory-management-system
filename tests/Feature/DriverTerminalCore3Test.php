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

class DriverTerminalCore3Test extends TestCase
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
            'driver_code' => 'DRV-000301',
            'driver_name' => 'Siddharth Varpe',
            'phone_number' => '9876543210',
            'email' => 'siddharth301@example.com',
            'license_class' => 'Heavy Commercial',
            'status' => 'available',
        ]);

        $this->driverB = Driver::create([
            'driver_code' => 'DRV-000302',
            'driver_name' => 'Ramesh Patil',
            'phone_number' => '9888877777',
            'email' => 'ramesh302@example.com',
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
            'vehicle_code' => 'VEH-000301',
            'vehicle_number' => 'MH12AU8888',
            'vehicle_type' => 'Truck 3D',
            'status' => 'available',
        ]);

        $customer = Customer::create([
            'customer_code' => 'CUST-301',
            'company_name' => 'Galaxy Electronics',
            'contact_person' => 'John Doe',
            'email' => 'john@galaxy.com',
            'phone' => '9876500000',
            'status' => 'active',
        ]);

        $salesOrder = SalesOrder::create([
            'order_number' => 'SO-2026-99301',
            'order_date' => now()->toDateString(),
            'customer_id' => $customer->id,
            'total_amount' => 75000.00,
            'status' => 'approved',
        ]);

        // Transport request assigned to Driver A in progress
        TransportRequest::create([
            'request_number' => 'DEL-170526-001',
            'sales_order_id' => $salesOrder->id,
            'driver_id' => $this->driverA->id,
            'vehicle_id' => $this->vehicle->id,
            'order_reference' => 'DEL-170526-001',
            'customer_name' => 'Galaxy Electronics',
            'delivery_address' => 'Baner Road, Pune - 411045',
            'delivery_city' => 'Pune',
            'status' => 'dispatched',
            'package_count' => 12,
            'weight_kg' => 150.00,
            'expected_delivery_date' => now()->toDateString(),
        ]);
    }

    public function test_driver_can_render_core3_deliveries_view_with_3d_truck_asset(): void
    {
        $response = $this->actingAs($this->userA)
            ->get(route('driver-terminal.deliveries.index', ['driver_code' => 'drv-000301']));

        $response->assertStatus(200);
        $response->assertViewIs('driver-terminal.deliveries.index');
        $response->assertSee('Deliveries');
        $response->assertSee('Manage all deliveries and track progress.');
        $response->assertSee('DEL-170526-001');
        $response->assertSee('Galaxy Electronics');
        $response->assertSee('truck-3d.png');
        $response->assertSee("Today's Delivery Summary", false);
    }

    public function test_core3_filtering_by_status_pills(): void
    {
        // In Progress filter tab
        $responseInProgress = $this->actingAs($this->userA)
            ->get(route('driver-terminal.deliveries.index', ['driver_code' => 'drv-000301', 'tab' => 'in_progress']));

        $responseInProgress->assertStatus(200);
        $responseInProgress->assertSee('DEL-170526-001');

        // Completed filter tab (empty for Driver A)
        $responseCompleted = $this->actingAs($this->userA)
            ->get(route('driver-terminal.deliveries.index', ['driver_code' => 'drv-000301', 'tab' => 'completed']));

        $responseCompleted->assertStatus(200);
        $responseCompleted->assertDontSee('DEL-170526-001');
    }

    public function test_core3_driver_isolation_security(): void
    {
        $response = $this->actingAs($this->userB)
            ->get(route('driver-terminal.deliveries.index', ['driver_code' => 'drv-000302']));

        $response->assertStatus(200);
        $response->assertDontSee('DEL-170526-001');
        $response->assertSee('NO ASSIGNED DELIVERIES');
    }
}

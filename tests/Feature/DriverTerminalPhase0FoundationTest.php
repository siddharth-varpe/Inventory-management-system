<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\TransportRequest;
use App\Models\SalesOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DriverTerminalPhase0FoundationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Driver $driver;
    protected Vehicle $vehicle;
    protected TransportRequest $transportRequest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'rajesh@stockmanager.com',
        ]);
        $this->actingAs($this->user);

        // Seed Driver Master
        $this->driver = Driver::create([
            'driver_code' => 'DRV-000001',
            'driver_name' => 'Rajesh Kumar',
            'employee_id' => 'EMP-DRV-0001',
            'license_class' => 'Heavy Commercial (HMV)',
            'phone_number' => '+91 98765 43210',
            'email' => 'rajesh@stockmanager.com',
            'status' => 'available',
        ]);

        // Seed Vehicle Master
        $this->vehicle = Vehicle::create([
            'vehicle_code' => 'VEH-000001',
            'vehicle_number' => 'MH12AU2233',
            'vehicle_type' => 'Heavy Commercial Vehicle',
            'load_capacity_kg' => 7500,
            'volume_capacity_m3' => 22.5,
            'status' => 'available',
        ]);

        // Seed Customer & Sales Order
        $customer = Customer::create([
            'customer_code' => 'CUST-TEST-01',
            'company_name' => 'Apex Logistics Corp',
            'contact_person' => 'Bob Builder',
            'email' => 'bob@apexlogistics.com',
            'phone' => '9888877777',
            'status' => 'active',
        ]);

        $salesOrder = SalesOrder::create([
            'order_number' => 'SO-2026-99001',
            'order_date' => now()->toDateString(),
            'customer_id' => $customer->id,
            'total_amount' => 25000.00,
            'status' => 'approved',
        ]);

        // Seed Transport Request
        $this->transportRequest = TransportRequest::create([
            'request_number' => 'TRN-2026-99001',
            'sales_order_id' => $salesOrder->id,
            'order_reference' => 'SO-2026-99001',
            'customer_name' => 'Apex Logistics Corp',
            'delivery_address' => 'Plot 42, Industrial Area, Pune',
            'delivery_city' => 'Pune',
            'contact_person' => 'Bob Builder',
            'phone_number' => '9888877777',
            'weight_kg' => 120.0,
            'volume_m3' => 1.2,
            'status' => 'ready_for_assignment',
            'warehouse_status' => 'seal_ready',
        ]);
    }

    /** @test */
    public function test_driver_and_vehicle_assignment_sets_status_to_assigned_not_dispatched(): void
    {
        $response = $this->postJson(route('transport.delivery-orders.assign', $this->transportRequest), [
            'driver_id' => $this->driver->id,
            'vehicle_id' => $this->vehicle->id,
            'instructions' => 'Handle with care',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->transportRequest->refresh();
        $this->driver->refresh();
        $this->vehicle->refresh();

        // Status MUST be driver_vehicle_assigned (ASSIGNED)
        $this->assertEquals('driver_vehicle_assigned', $this->transportRequest->status);
        $this->assertNotEquals('dispatched', $this->transportRequest->status);
        $this->assertEquals('on_delivery', $this->driver->status);
        $this->assertEquals('on_trip', $this->vehicle->status);
    }

    /** @test */
    public function test_driver_terminal_foundational_routes_resolve_cleanly(): void
    {
        // 1. Guest login route
        $loginRes = $this->get(route('driver-terminal.login'));
        $loginRes->assertOk();

        // 2. Authenticated terminal workspace
        $indexRes = $this->get(route('driver-terminal.index'));
        $indexRes->assertOk();
    }

    /** @test */
    public function test_driver_master_id_format_integrity(): void
    {
        $this->assertNotNull($this->driver->driver_code);
        $this->assertMatchesRegularExpression('/^DRV-\d{6}$/', $this->driver->driver_code);
        $this->assertEquals('DRV-000001', $this->driver->driver_code);
    }
}

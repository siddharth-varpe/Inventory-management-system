<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\Customer;
use App\Models\SalesOrder;
use App\Models\TransportRequest;
use App\Models\DriverVehicleAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TransportAssignmentLockAndViewProfileTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;
    protected Driver $driverA;
    protected Driver $driverB;
    protected Vehicle $vehicleA;
    protected Vehicle $vehicleB;
    protected Customer $customer;
    protected SalesOrder $salesOrder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = User::firstOrCreate(
            ['email' => 'transport_mgr_lock_test@example.com'],
            ['name' => 'Transport Test Manager', 'password' => bcrypt('password')]
        );

        $this->customer = Customer::create([
            'customer_code' => 'CUST-LK-0001',
            'company_name' => 'Lock Test Customer',
            'email' => 'customer_lock@example.com',
            'phone' => '+919000000000',
            'status' => 'active',
        ]);

        $this->salesOrder = SalesOrder::create([
            'order_number' => 'SO-2026-LK001',
            'customer_id' => $this->customer->id,
            'salesperson_id' => $this->manager->id,
            'created_by' => $this->manager->id,
            'order_date' => now(),
            'status' => 'confirmed',
            'grand_total' => 1000.00,
        ]);

        $seq = rand(1000, 9999);
        $this->driverA = Driver::create([
            'driver_code' => "DRV-LK-{$seq}A",
            'driver_name' => "Driver Alpha {$seq}",
            'phone_number' => '9876543210',
            'employee_id' => "EMP-LK-{$seq}A",
            'license_class' => 'Heavy Commercial (HMV)',
            'driving_license_number' => "DL-LK-{$seq}A",
            'license_expiry_date' => now()->addYears(3),
            'status' => 'available',
        ]);

        $this->driverB = Driver::create([
            'driver_code' => "DRV-LK-{$seq}B",
            'driver_name' => "Driver Beta {$seq}",
            'phone_number' => '9876543211',
            'employee_id' => "EMP-LK-{$seq}B",
            'license_class' => 'Heavy Commercial (HMV)',
            'driving_license_number' => "DL-LK-{$seq}B",
            'license_expiry_date' => now()->addYears(3),
            'status' => 'available',
        ]);

        $this->vehicleA = Vehicle::create([
            'vehicle_code' => "VEH-LK-{$seq}A",
            'vehicle_number' => "MH12LK{$seq}A",
            'vehicle_type' => 'Medium Commercial Vehicle',
            'load_capacity_kg' => 5000,
            'status' => 'available',
        ]);

        $this->vehicleB = Vehicle::create([
            'vehicle_code' => "VEH-LK-{$seq}B",
            'vehicle_number' => "MH12LK{$seq}B",
            'vehicle_type' => 'Heavy Commercial Vehicle',
            'load_capacity_kg' => 8000,
            'status' => 'available',
        ]);
    }

    /** @test */
    public function driver_and_vehicle_can_be_reassigned_before_dispatch()
    {
        $seq = rand(10000, 99999);
        $delivery = TransportRequest::create([
            'sales_order_id' => $this->salesOrder->id,
            'request_number' => "TRN-LK-{$seq}",
            'order_reference' => "SO-LK-{$seq}",
            'customer_name' => 'Lock Test Customer',
            'delivery_address' => '123 Lock Way',
            'delivery_city' => 'Pune',
            'status' => 'ready_for_assignment',
            'priority' => 'normal',
            'weight_kg' => 1000,
            'volume_m3' => 2,
            'warehouse_completed_at' => now(),
            'warehouse_status' => 'ready_for_dispatch',
        ]);

        // 1. Initial Assignment: Driver A + Vehicle A
        $response1 = $this->actingAs($this->manager)
            ->postJson("/transport/delivery-orders/{$delivery->id}/assign", [
                'driver_id' => $this->driverA->id,
                'vehicle_id' => $this->vehicleA->id,
            ]);

        $response1->assertStatus(200);
        $response1->assertJson(['success' => true]);

        $delivery->refresh();
        $this->assertEquals($this->driverA->id, $delivery->driver_id);
        $this->assertEquals($this->vehicleA->id, $delivery->vehicle_id);

        // 2. Reassignment Before Dispatch: Driver B + Vehicle B
        $response2 = $this->actingAs($this->manager)
            ->postJson("/transport/delivery-orders/{$delivery->id}/reassign", [
                'driver_id' => $this->driverB->id,
                'vehicle_id' => $this->vehicleB->id,
                'reassignment_reason' => 'Driver A scheduling conflict',
            ]);

        $response2->assertStatus(200);
        $response2->assertJson(['success' => true]);

        $delivery->refresh();
        $this->assertEquals($this->driverB->id, $delivery->driver_id);
        $this->assertEquals($this->vehicleB->id, $delivery->vehicle_id);

        // Verify Driver A and Vehicle A released back to available
        $this->driverA->refresh();
        $this->vehicleA->refresh();
        $this->assertEquals('available', $this->driverA->status);
        $this->assertEquals('available', $this->vehicleA->status);
    }

    /** @test */
    public function driver_and_vehicle_reassignment_is_rejected_on_backend_after_dispatch()
    {
        $seq = rand(10000, 99999);
        $delivery = TransportRequest::create([
            'sales_order_id' => $this->salesOrder->id,
            'request_number' => "TRN-LK-{$seq}",
            'order_reference' => "SO-LK-{$seq}",
            'customer_name' => 'Dispatched Lock Customer',
            'delivery_address' => '456 Dispatch Blvd',
            'delivery_city' => 'Mumbai',
            'status' => 'dispatched',
            'driver_id' => $this->driverA->id,
            'driver_name' => $this->driverA->driver_name,
            'vehicle_id' => $this->vehicleA->id,
            'vehicle_number' => $this->vehicleA->vehicle_number,
            'priority' => 'high',
            'weight_kg' => 1200,
            'volume_m3' => 3,
            'dispatched_at' => now(),
            'dispatch_number' => "DSP-LK-{$seq}",
        ]);

        // Attempt Reassignment via reassign endpoint
        $response = $this->actingAs($this->manager)
            ->postJson("/transport/delivery-orders/{$delivery->id}/reassign", [
                'driver_id' => $this->driverB->id,
                'vehicle_id' => $this->vehicleB->id,
                'reassignment_reason' => 'Malicious bypass attempt',
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Reassignment is not allowed because this delivery has already been dispatched.',
        ]);

        // Database verification: Driver A and Vehicle A MUST remain unchanged
        $delivery->refresh();
        $this->assertEquals($this->driverA->id, $delivery->driver_id);
        $this->assertEquals($this->vehicleA->id, $delivery->vehicle_id);
    }

    /** @test */
    public function direct_assignment_endpoints_are_rejected_on_backend_after_dispatch()
    {
        $seq = rand(10000, 99999);
        $delivery = TransportRequest::create([
            'sales_order_id' => $this->salesOrder->id,
            'request_number' => "TRN-LK-{$seq}",
            'order_reference' => "SO-LK-{$seq}",
            'customer_name' => 'Direct Assign Bypass Customer',
            'delivery_address' => '789 Direct Street',
            'delivery_city' => 'Thane',
            'status' => 'dispatched',
            'driver_id' => $this->driverA->id,
            'driver_name' => $this->driverA->driver_name,
            'vehicle_id' => $this->vehicleA->id,
            'vehicle_number' => $this->vehicleA->vehicle_number,
            'dispatched_at' => now(),
            'dispatch_number' => "DSP-LK-{$seq}",
        ]);

        // Attempt assign endpoint
        $responseAssign = $this->actingAs($this->manager)
            ->postJson("/transport/delivery-orders/{$delivery->id}/assign", [
                'driver_id' => $this->driverB->id,
                'vehicle_id' => $this->vehicleB->id,
            ]);

        $responseAssign->assertStatus(422);
        $responseAssign->assertJson([
            'success' => false,
            'message' => 'Reassignment is not allowed because this delivery has already been dispatched.',
        ]);

        // Attempt single driver assign endpoint
        $responseDriver = $this->actingAs($this->manager)
            ->post("/transport/{$delivery->id}/assign-driver", [
                'driver_id' => $this->driverB->id,
            ]);
        $responseDriver->assertSessionHas('error');

        // Database verification: Driver A and Vehicle A remain untouched
        $delivery->refresh();
        $this->assertEquals($this->driverA->id, $delivery->driver_id);
        $this->assertEquals($this->vehicleA->id, $delivery->vehicle_id);
    }

    /** @test */
    public function view_profile_endpoint_returns_correct_delivery_profile_data()
    {
        $seq1 = rand(10000, 99999);
        $delivery1 = TransportRequest::create([
            'sales_order_id' => $this->salesOrder->id,
            'request_number' => "TRN-LK-{$seq1}",
            'order_reference' => "SO-LK-{$seq1}",
            'customer_name' => 'Customer Alpha',
            'delivery_address' => 'Alpha Street',
            'delivery_city' => 'Nagpur',
            'status' => 'ready_for_assignment',
            'priority' => 'urgent',
            'weight_kg' => 800,
            'volume_m3' => 1.5,
        ]);

        $seq2 = rand(10000, 99999);
        $delivery2 = TransportRequest::create([
            'sales_order_id' => $this->salesOrder->id,
            'request_number' => "TRN-LK-{$seq2}",
            'order_reference' => "SO-LK-{$seq2}",
            'customer_name' => 'Customer Beta',
            'delivery_address' => 'Beta Avenue',
            'delivery_city' => 'Nashik',
            'status' => 'dispatched',
            'driver_id' => $this->driverA->id,
            'driver_name' => $this->driverA->driver_name,
            'vehicle_id' => $this->vehicleA->id,
            'vehicle_number' => $this->vehicleA->vehicle_number,
            'priority' => 'normal',
            'weight_kg' => 2000,
            'volume_m3' => 4,
            'dispatched_at' => now(),
            'dispatch_number' => "DSP-LK-{$seq2}",
        ]);

        // Request Delivery 1 profile
        $response1 = $this->actingAs($this->manager)
            ->getJson("/transport/delivery-orders/{$delivery1->id}");

        $response1->assertStatus(200);
        $response1->assertJson([
            'id' => $delivery1->id,
            'order_reference' => "SO-LK-{$seq1}",
            'customer_name' => 'Customer Alpha',
            'delivery_city' => 'Nagpur',
            'status' => 'ready_for_assignment',
        ]);

        // Request Delivery 2 profile
        $response2 = $this->actingAs($this->manager)
            ->getJson("/transport/delivery-orders/{$delivery2->id}");

        $response2->assertStatus(200);
        $response2->assertJson([
            'id' => $delivery2->id,
            'order_reference' => "SO-LK-{$seq2}",
            'customer_name' => 'Customer Beta',
            'delivery_city' => 'Nashik',
            'status' => 'dispatched',
            'driver' => [
                'id' => $this->driverA->id,
                'driver_code' => $this->driverA->driver_code,
            ],
            'vehicle' => [
                'id' => $this->vehicleA->id,
                'vehicle_number' => $this->vehicleA->vehicle_number,
            ],
        ]);
    }
}

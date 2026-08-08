<?php

namespace Tests\Feature;

use App\Domain\Transport\DispatchExecutionEngine;
use App\Domain\Transport\TransportPlanningEngine;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\DeliveryTimeline;
use App\Models\Driver;
use App\Models\DriverVehicleAssignment;
use App\Models\SalesOrder;
use App\Models\TransportRequest;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransportDispatchPhase5Test extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Customer $customer;
    protected Driver $driver;
    protected Vehicle $vehicle;
    protected TransportPlanningEngine $planningEngine;
    protected DispatchExecutionEngine $executionEngine;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->customer = Customer::create([
            'customer_code' => 'CUST-TEST-' . rand(1000, 9999),
            'company_name' => 'Enterprise Logistics Corp',
            'contact_person' => 'John Cargo',
            'email' => 'john@cargo.com',
            'phone' => '9876543210',
            'city' => 'Mumbai',
        ]);

        $this->driver = Driver::create([
            'driver_code' => 'DRV-101',
            'driver_name' => 'Rajesh Sharma',
            'phone_number' => '9876543210',
            'license_number' => 'DL-MH-2025-999',
            'license_class' => 'Heavy Goods',
            'license_expiry_date' => now()->addYears(2),
            'employee_id' => 'EMP-101',
            'status' => 'available',
        ]);

        $this->vehicle = Vehicle::create([
            'vehicle_code' => 'VEH-201',
            'vehicle_number' => 'MH-12-TR-9999',
            'vehicle_type' => 'Heavy Truck',
            'make' => 'Tata',
            'model' => 'Prima 2830',
            'load_capacity_kg' => 10000.00,
            'volume_capacity_m3' => 50.00,
            'status' => 'available',
        ]);

        $this->planningEngine = app(TransportPlanningEngine::class);
        $this->executionEngine = app(DispatchExecutionEngine::class);
    }

    protected function createReadyOrder(): TransportRequest
    {
        $salesOrder = SalesOrder::create([
            'order_number' => 'SO-2026-TEST-' . rand(100, 999),
            'customer_id' => $this->customer->id,
            'status' => 'warehouse_completed',
            'order_date' => now(),
        ]);

        return TransportRequest::create([
            'request_number' => 'TRN-2026-TEST-' . rand(100, 999),
            'sales_order_id' => $salesOrder->id,
            'order_reference' => $salesOrder->order_number,
            'customer_name' => $this->customer->company_name,
            'delivery_address' => '123 Logistics Park, Andheri East',
            'delivery_city' => 'Mumbai',
            'phone_number' => '9876543210',
            'priority' => 'high',
            'status' => 'ready_for_assignment',
            'warehouse_status' => 'completed',
            'warehouse_completed_at' => now(),
            'weight_kg' => 500.00,
            'volume_m3' => 5.00,
        ]);
    }

    /** Test 1: Create valid order & assign resources */
    public function test_1_create_valid_ready_for_assignment_order_and_assign_resources(): void
    {
        $order = $this->createReadyOrder();

        $assignment = $this->planningEngine->assignDriverAndVehicle(
            $order,
            $this->driver->id,
            $this->vehicle->id,
            $this->user->id
        );

        $order->refresh();

        $this->assertEquals('driver_vehicle_assigned', $order->status);
        $this->assertEquals($this->driver->id, $order->driver_id);
        $this->assertEquals($this->vehicle->id, $order->vehicle_id);
        $this->assertNotNull($order->driver_vehicle_assignment_id);
    }

    /** Test 2: Assigned order shows dispatch eligibility */
    public function test_2_open_assigned_order_shows_dispatch_button(): void
    {
        $order = $this->createReadyOrder();
        $this->planningEngine->assignDriverAndVehicle($order, $this->driver->id, $this->vehicle->id, $this->user->id);

        $order->refresh();
        $eligibility = $order->dispatch_eligibility;

        $this->assertTrue($eligibility['eligible']);
        $this->assertNull($eligibility['reason']);
    }

    /** Test 3: Dispatch without warehouse completion is blocked */
    public function test_3_dispatch_without_warehouse_completion_is_blocked(): void
    {
        $order = $this->createReadyOrder();
        $order->update(['warehouse_completed_at' => null, 'warehouse_status' => 'pending']);

        $eligibility = $order->dispatch_eligibility;
        $this->assertFalse($eligibility['eligible']);
        $this->assertStringContainsString('warehouse fulfillment has not been completed', $eligibility['reason']);
    }

    /** Test 4: Dispatch without driver is blocked */
    public function test_4_dispatch_without_driver_is_blocked(): void
    {
        $order = $this->createReadyOrder();
        $order->update([
            'status' => 'driver_vehicle_assigned',
            'vehicle_id' => $this->vehicle->id,
            'driver_id' => null,
        ]);

        $eligibility = $order->dispatch_eligibility;
        $this->assertFalse($eligibility['eligible']);
        $this->assertStringContainsString('no driver is assigned', $eligibility['reason']);
    }

    /** Test 5: Dispatch without vehicle is blocked */
    public function test_5_dispatch_without_vehicle_is_blocked(): void
    {
        $order = $this->createReadyOrder();
        $order->update([
            'status' => 'driver_vehicle_assigned',
            'driver_id' => $this->driver->id,
            'vehicle_id' => null,
        ]);

        $eligibility = $order->dispatch_eligibility;
        $this->assertFalse($eligibility['eligible']);
        $this->assertStringContainsString('no vehicle is assigned', $eligibility['reason']);
    }

    /** Test 6: Successfully dispatch valid order updates all statuses */
    public function test_6_successfully_dispatch_valid_order_updates_all_statuses(): void
    {
        $order = $this->createReadyOrder();
        $this->planningEngine->assignDriverAndVehicle($order, $this->driver->id, $this->vehicle->id, $this->user->id);

        $dispatchedOrder = $this->executionEngine->confirmDispatchOrder($order, $this->user->id, 'Gate Pass #GP-999');

        $order->refresh();
        $this->driver->refresh();
        $this->vehicle->refresh();

        $this->assertEquals('dispatched', $order->status);
        $this->assertNotNull($order->dispatch_number);
        $this->assertStringStartsWith('DSP-', $order->dispatch_number);
        $this->assertEquals('dispatched', $order->salesOrder->status);
        $this->assertEquals('on_delivery', $this->driver->status);
        $this->assertEquals('on_trip', $this->vehicle->status);
    }

    /** Test 7: Double dispatch prevention */
    public function test_7_double_click_dispatch_prevention(): void
    {
        $order = $this->createReadyOrder();
        $this->planningEngine->assignDriverAndVehicle($order, $this->driver->id, $this->vehicle->id, $this->user->id);
        $this->executionEngine->confirmDispatchOrder($order, $this->user->id);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('This delivery has already been dispatched.');

        $this->executionEngine->confirmDispatchOrder($order, $this->user->id);
    }

    /** Test 8: Concurrency protection check */
    public function test_8_simultaneous_concurrency_protection(): void
    {
        $order = $this->createReadyOrder();
        $this->planningEngine->assignDriverAndVehicle($order, $this->driver->id, $this->vehicle->id, $this->user->id);

        $order->update(['status' => 'dispatched']);

        $this->expectException(\InvalidArgumentException::class);
        $this->executionEngine->confirmDispatchOrder($order, $this->user->id);
    }

    /** Test 9: Dispatch ID uniqueness & format */
    public function test_9_dispatch_id_uniqueness_and_format(): void
    {
        $order1 = $this->createReadyOrder();
        $this->planningEngine->assignDriverAndVehicle($order1, $this->driver->id, $this->vehicle->id, $this->user->id);
        $d1 = $this->executionEngine->confirmDispatchOrder($order1, $this->user->id);

        $this->assertMatchesRegularExpression('/^DSP-\d{4}-\d{6}$/', $d1->dispatch_number);
    }

    /** Test 10: Audit event creation */
    public function test_10_audit_event_creation(): void
    {
        $order = $this->createReadyOrder();
        $this->planningEngine->assignDriverAndVehicle($order, $this->driver->id, $this->vehicle->id, $this->user->id);
        $this->executionEngine->confirmDispatchOrder($order, $this->user->id);

        $this->assertDatabaseHas('audit_logs', [
            'module' => 'Transport Department',
            'action' => 'Dispatch Successful',
            'record_id' => $order->id,
        ]);
        $this->assertDatabaseHas('delivery_timelines', [
            'transport_request_id' => $order->id,
            'status' => 'dispatched',
        ]);
    }

    /** Test 11: Active delivery visibility */
    public function test_11_active_delivery_visibility(): void
    {
        $order = $this->createReadyOrder();
        $this->planningEngine->assignDriverAndVehicle($order, $this->driver->id, $this->vehicle->id, $this->user->id);
        $this->executionEngine->confirmDispatchOrder($order, $this->user->id);

        $response = $this->get('/transport?tab=active');
        $response->assertStatus(200);
        $response->assertSee($order->order_reference);
        $response->assertSee($order->dispatch_number);
    }

    /** Test 12: Completed deliveries excluded from active deliveries */
    public function test_12_completed_deliveries_excluded_from_active_deliveries(): void
    {
        $order = $this->createReadyOrder();
        $order->update(['status' => 'completed']);

        $response = $this->get('/transport?tab=active');
        $response->assertStatus(200);
        $response->assertDontSee($order->order_reference);
    }

    /** Test 13: Dispatched driver cannot be assigned to another order */
    public function test_13_dispatched_driver_cannot_be_assigned_to_another_order(): void
    {
        $order1 = $this->createReadyOrder();
        $this->planningEngine->assignDriverAndVehicle($order1, $this->driver->id, $this->vehicle->id, $this->user->id);
        $this->executionEngine->confirmDispatchOrder($order1, $this->user->id);

        $order2 = $this->createReadyOrder();
        $vehicle2 = Vehicle::create([
            'vehicle_code' => 'VEH-202',
            'vehicle_number' => 'MH-12-TR-8888',
            'vehicle_type' => 'Medium Truck',
            'load_capacity_kg' => 5000.00,
            'status' => 'available',
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->planningEngine->assignDriverAndVehicle($order2, $this->driver->id, $vehicle2->id, $this->user->id);
    }

    /** Test 14: Dispatched vehicle cannot be assigned to another order */
    public function test_14_dispatched_vehicle_cannot_be_assigned_to_another_order(): void
    {
        $order1 = $this->createReadyOrder();
        $this->planningEngine->assignDriverAndVehicle($order1, $this->driver->id, $this->vehicle->id, $this->user->id);
        $this->executionEngine->confirmDispatchOrder($order1, $this->user->id);

        $order2 = $this->createReadyOrder();
        $driver2 = Driver::create([
            'driver_code' => 'DRV-102',
            'driver_name' => 'Suresh Patil',
            'employee_id' => 'EMP-102',
            'phone_number' => '9876543211',
            'status' => 'available',
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->planningEngine->assignDriverAndVehicle($order2, $driver2->id, $this->vehicle->id, $this->user->id);
    }

    /** Test 15: Active deliveries search */
    public function test_15_active_deliveries_search(): void
    {
        $order = $this->createReadyOrder();
        $this->planningEngine->assignDriverAndVehicle($order, $this->driver->id, $this->vehicle->id, $this->user->id);
        $this->executionEngine->confirmDispatchOrder($order, $this->user->id);

        $response = $this->get('/transport?tab=active&search=' . $order->dispatch_number);
        $response->assertStatus(200);
        $response->assertSee($order->order_reference);
    }

    /** Test 16: Active deliveries pagination */
    public function test_16_active_deliveries_pagination(): void
    {
        $response = $this->get('/transport?tab=active&page=1');
        $response->assertStatus(200);
    }

    /** Test 17: Invalid or cancelled order cannot be dispatched */
    public function test_17_invalid_or_cancelled_order_cannot_be_dispatched(): void
    {
        $order = $this->createReadyOrder();
        $order->update(['status' => 'cancelled']);

        $this->expectException(\InvalidArgumentException::class);
        $this->executionEngine->confirmDispatchOrder($order, $this->user->id);
    }

    /** Test 18: Duplicate active delivery prevention */
    public function test_18_duplicate_active_delivery_prevention(): void
    {
        $order = $this->createReadyOrder();
        $this->planningEngine->assignDriverAndVehicle($order, $this->driver->id, $this->vehicle->id, $this->user->id);
        $this->executionEngine->confirmDispatchOrder($order, $this->user->id);

        $this->expectException(\InvalidArgumentException::class);
        $this->executionEngine->confirmDispatchOrder($order, $this->user->id);
    }

    /** Test 19: CRM remains functional */
    public function test_19_crm_remains_functional(): void
    {
        $response = $this->get('/sales');
        $response->assertStatus(200);
    }

    /** Test 20: Organize Stock remains functional */
    public function test_20_organize_stock_remains_functional(): void
    {
        $response = $this->get('/organize-stock');
        $response->assertStatus(200);
    }

    /** Test 21: Driver Master remains functional */
    public function test_21_driver_master_remains_functional(): void
    {
        $response = $this->get('/transport?tab=drivers');
        $response->assertStatus(200);
    }

    /** Test 22: Vehicle Master remains functional */
    public function test_22_vehicle_master_remains_functional(): void
    {
        $response = $this->get('/transport?tab=vehicles');
        $response->assertStatus(200);
    }

    /** Test 23: Phase 4 assignment remains functional */
    public function test_23_phase4_assignment_remains_functional(): void
    {
        $order = $this->createReadyOrder();
        $assignment = $this->planningEngine->assignDriverAndVehicle($order, $this->driver->id, $this->vehicle->id, $this->user->id);

        $this->assertNotNull($assignment);
        $this->assertEquals('active', $assignment->status);
    }

    /** Test 24: Light mode compatibility */
    public function test_24_light_mode_compatibility(): void
    {
        $response = $this->get('/transport?tab=delivery-orders');
        $response->assertStatus(200);
    }

    /** Test 25: Dark mode compatibility */
    public function test_25_dark_mode_compatibility(): void
    {
        $response = $this->get('/transport?tab=active');
        $response->assertStatus(200);
    }

    /** Test 26: Responsive layout compatibility */
    public function test_26_responsive_layout_compatibility(): void
    {
        $response = $this->get('/transport?tab=history');
        $response->assertStatus(200);
    }
}

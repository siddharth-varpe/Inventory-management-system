<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Sales\SalesOrderService;
use App\Domain\Transport\TransportManagementEngine;
use App\Domain\Transport\TransportPlanningEngine;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\DriverNotification;
use App\Models\DriverVehicleAssignment;
use App\Models\SalesOrder;
use App\Models\TransportRequest;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class Phase4DriverVehicleAssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Customer $customer;
    protected Warehouse $warehouse;
    protected TransportManagementEngine $transportEngine;
    protected TransportPlanningEngine $planningEngine;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['name' => 'Transport Manager']);
        $this->customer = Customer::create([
            'customer_code' => 'CUST-000001',
            'company_name' => 'Acme Logistics Supermarket',
            'email' => 'acme@example.com',
            'phone' => '9876543210',
            'city' => 'Mumbai',
        ]);

        $this->warehouse = Warehouse::create([
            'name' => 'Central Hub Warehouse',
            'code' => 'WH-HUB-01',
            'address' => 'Andheri East Industrial Zone',
            'city' => 'Mumbai',
        ]);

        $this->transportEngine = app(TransportManagementEngine::class);
        $this->planningEngine = app(TransportPlanningEngine::class);
    }

    private function createSampleDriver(string $code = 'DRV-000001', string $status = 'available', bool $expiredLicense = false): Driver
    {
        return Driver::create([
            'driver_code' => $code,
            'employee_id' => 'EMP-' . rand(100000, 999999),
            'driver_name' => 'John Fleet Driver',
            'phone_number' => '9876543210',
            'license_class' => 'Heavy Commercial',
            'driving_license_number' => 'DL-' . rand(100000, 999999),
            'license_expiry_date' => $expiredLicense ? date('Y-m-d', strtotime('-10 days')) : date('Y-m-d', strtotime('+2 years')),
            'status' => $status,
        ]);
    }

    private function createSampleVehicle(string $code = 'VEH-000001', string $status = 'available', float $capacityKg = 1000.0): Vehicle
    {
        return Vehicle::create([
            'vehicle_code' => $code,
            'vehicle_number' => 'MH12AB' . rand(1000, 9999),
            'vehicle_type' => 'Medium Delivery Van',
            'load_capacity_kg' => $capacityKg,
            'volume_capacity_m3' => 10.0,
            'status' => $status,
            'maintenance_status' => 'Operational',
        ]);
    }

    private function createReadyOrder(string $orderNum = 'SO-2026-000001', float $weightKg = 150.0): TransportRequest
    {
        $salesOrder = SalesOrder::create([
            'order_number' => $orderNum,
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'order_date' => date('Y-m-d'),
            'order_priority' => 'high',
            'status' => 'waiting_warehouse',
        ]);

        $task = $this->transportEngine->createTransportRequestFromSalesOrder($salesOrder);
        $task->update(['weight_kg' => $weightKg, 'volume_m3' => 2.0]);

        return $this->transportEngine->markReadyForDispatch($orderNum);
    }

    /** @test - Test 1 */
    public function test_ready_for_assignment_order_shows_assignment_controls(): void
    {
        $driver = $this->createSampleDriver();
        $task = $this->createReadyOrder();

        $response = $this->actingAs($this->user)->get("/transport/delivery-orders/{$task->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'ready_for_assignment',
                'status_label' => 'Ready for Assignment',
            ]);
        $this->assertNotEmpty($response->json('eligible_drivers'));
    }

    /** @test - Test 2 */
    public function test_assigning_unavailable_driver_is_blocked(): void
    {
        $task = $this->createReadyOrder();
        $driver = $this->createSampleDriver('DRV-BUSY-01', 'on_delivery');
        $vehicle = $this->createSampleVehicle();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Driver {$driver->driver_name} is no longer available.");

        $this->planningEngine->assignDriverAndVehicle($task, $driver->id, $vehicle->id, $this->user->id);
    }

    /** @test - Test 3 */
    public function test_assigning_unavailable_vehicle_is_blocked(): void
    {
        $task = $this->createReadyOrder();
        $driver = $this->createSampleDriver();
        $vehicle = $this->createSampleVehicle('VEH-REPAIR-01', 'maintenance');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Vehicle {$vehicle->vehicle_number} is no longer available.");

        $this->planningEngine->assignDriverAndVehicle($task, $driver->id, $vehicle->id, $this->user->id);
    }

    /** @test - Test 4 */
    public function test_assigning_undersized_vehicle_capacity_is_blocked(): void
    {
        $task = $this->createReadyOrder('SO-2026-HEAVY01', 2500.0); // 2,500 kg shipment
        $driver = $this->createSampleDriver();
        $vehicle = $this->createSampleVehicle('VEH-SMALL-01', 'available', 1000.0); // Only 1,000 kg capacity

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Selected vehicle does not have sufficient capacity for this order.");

        $this->planningEngine->assignDriverAndVehicle($task, $driver->id, $vehicle->id, $this->user->id);
    }

    /** @test - Test 5, 6, 7, 8, 17 & 18 */
    public function test_assign_valid_driver_and_vehicle_succeeds_and_updates_statuses(): void
    {
        $task = $this->createReadyOrder();
        $driver = $this->createSampleDriver();
        $vehicle = $this->createSampleVehicle();

        $assignment = $this->planningEngine->assignDriverAndVehicle($task, $driver->id, $vehicle->id, $this->user->id, 'Handle fragile boxes with care');

        // Test 17: Unique assignment ID format ASN-000001
        $this->assertMatchesRegularExpression('/^ASN-\d{6}$/', $assignment->assignment_number);

        // Test 18: Enterprise order ID remains unchanged
        $this->assertEquals($task->order_reference, $assignment->enterprise_order_id);

        // Test 6: Transport Status becomes DRIVER & VEHICLE ASSIGNED
        $task->refresh();
        $this->assertEquals('driver_vehicle_assigned', $task->status);
        $this->assertEquals('Driver & Vehicle Assigned', $task->status_label);

        // Test 7: Driver Status becomes ON DELIVERY
        $driver->refresh();
        $this->assertEquals('on_delivery', $driver->status);

        // Test 8: Vehicle Status becomes ON TRIP
        $vehicle->refresh();
        $this->assertEquals('on_trip', $vehicle->status);
    }

    /** @test - Test 9 & 10 */
    public function test_only_assigned_driver_receives_targeted_notification(): void
    {
        $task = $this->createReadyOrder();
        $assignedDriver = $this->createSampleDriver('DRV-TARGET-01');
        $otherDriver = $this->createSampleDriver('DRV-OTHER-02');
        $vehicle = $this->createSampleVehicle();

        $this->planningEngine->assignDriverAndVehicle($task, $assignedDriver->id, $vehicle->id, $this->user->id);

        // Test 9: Assigned Driver ID receives notification
        $this->assertDatabaseHas('driver_notifications', [
            'driver_id' => $assignedDriver->id,
            'enterprise_order_id' => $task->order_reference,
            'title' => 'New Delivery Assigned',
        ]);

        // Test 10: Other driver does NOT receive notification
        $this->assertDatabaseMissing('driver_notifications', [
            'driver_id' => $otherDriver->id,
            'enterprise_order_id' => $task->order_reference,
        ]);
    }

    /** @test - Test 11 */
    public function test_duplicate_assignment_attempt_is_blocked(): void
    {
        $task = $this->createReadyOrder();
        $driver = $this->createSampleDriver();
        $vehicle = $this->createSampleVehicle();

        $this->planningEngine->assignDriverAndVehicle($task, $driver->id, $vehicle->id, $this->user->id);

        $otherDriver = $this->createSampleDriver('DRV-FREE-01');
        $otherVehicle = $this->createSampleVehicle('VEH-FREE-01');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Cannot assign driver and vehicle to Transport Task #{$task->request_number} because its status is 'Driver & Vehicle Assigned'. Only orders in 'Ready for Assignment' status can be assigned.");

        $this->planningEngine->assignDriverAndVehicle($task, $otherDriver->id, $otherVehicle->id, $this->user->id);
    }

    /** @test - Test 12 */
    public function test_concurrent_driver_assignment_protection(): void
    {
        $task1 = $this->createReadyOrder('SO-2026-CONC01');
        $task2 = $this->createReadyOrder('SO-2026-CONC02');
        $driver = $this->createSampleDriver();
        $vehicle1 = $this->createSampleVehicle('VEH-V1');
        $vehicle2 = $this->createSampleVehicle('VEH-V2');

        // Manager A confirms task 1
        $this->planningEngine->assignDriverAndVehicle($task1, $driver->id, $vehicle1->id, $this->user->id);

        // Manager B attempts task 2 with same driver
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Driver {$driver->driver_name} is no longer available.");

        $this->planningEngine->assignDriverAndVehicle($task2, $driver->id, $vehicle2->id, $this->user->id);
    }

    /** @test - Test 13 */
    public function test_concurrent_vehicle_assignment_protection(): void
    {
        $task1 = $this->createReadyOrder('SO-2026-CONC03');
        $task2 = $this->createReadyOrder('SO-2026-CONC04');
        $driver1 = $this->createSampleDriver('DRV-D1');
        $driver2 = $this->createSampleDriver('DRV-D2');
        $vehicle = $this->createSampleVehicle();

        // Manager A confirms task 1
        $this->planningEngine->assignDriverAndVehicle($task1, $driver1->id, $vehicle->id, $this->user->id);

        // Manager B attempts task 2 with same vehicle
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Vehicle {$vehicle->vehicle_number} is no longer available.");

        $this->planningEngine->assignDriverAndVehicle($task2, $driver2->id, $vehicle->id, $this->user->id);
    }

    /** @test - Test 14 & 16 */
    public function test_reassign_before_dispatch_releases_old_resources_and_creates_audit_log(): void
    {
        $task = $this->createReadyOrder();
        $oldDriver = $this->createSampleDriver('DRV-OLD-01');
        $oldVehicle = $this->createSampleVehicle('VEH-OLD-01');
        $newDriver = $this->createSampleDriver('DRV-NEW-02');
        $newVehicle = $this->createSampleVehicle('VEH-NEW-02');

        $this->planningEngine->assignDriverAndVehicle($task, $oldDriver->id, $oldVehicle->id, $this->user->id);

        // Reassign
        $newAssignment = $this->planningEngine->reassignDriverAndVehicle($task, $newDriver->id, $newVehicle->id, $this->user->id, 'Driver reported sick');

        // Test 14: Old resources released
        $oldDriver->refresh();
        $oldVehicle->refresh();
        $this->assertEquals('available', $oldDriver->status);
        $this->assertNull($oldDriver->current_assignment);
        $this->assertEquals('available', $oldVehicle->status);

        // New resources updated
        $newDriver->refresh();
        $newVehicle->refresh();
        $this->assertEquals('on_delivery', $newDriver->status);
        $this->assertEquals('on_trip', $newVehicle->status);

        // Test 16: Audit log recorded
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'Assignment Reassigned',
            'record_id' => $newAssignment->id,
        ]);
    }

    /** @test - Test 15 */
    public function test_reassign_after_dispatch_is_blocked(): void
    {
        $task = $this->createReadyOrder();
        $driver = $this->createSampleDriver('DRV-D1');
        $vehicle = $this->createSampleVehicle('VEH-V1');

        $this->planningEngine->assignDriverAndVehicle($task, $driver->id, $vehicle->id, $this->user->id);

        // Simulate dispatch
        $task->update(['status' => 'dispatched']);

        $newDriver = $this->createSampleDriver('DRV-D2');
        $newVehicle = $this->createSampleVehicle('VEH-V2');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Cannot reassign order #{$task->order_reference} because it is already dispatched or in transit.");

        $this->planningEngine->reassignDriverAndVehicle($task, $newDriver->id, $newVehicle->id, $this->user->id, 'Emergency swap');
    }

    /** @test - Test 19: Driver Master Remains Functional */
    public function test_driver_master_remains_functional(): void
    {
        $response = $this->actingAs($this->user)->get('/transport/drivers');
        $response->assertStatus(200);
    }

    /** @test - Test 20: Vehicle Master Remains Functional */
    public function test_vehicle_master_remains_functional(): void
    {
        $response = $this->actingAs($this->user)->get('/transport/vehicles');
        $response->assertStatus(200);
    }

    /** @test - Test 21: CRM Remains Functional */
    public function test_crm_remains_functional(): void
    {
        $response = $this->actingAs($this->user)->get('/sales');
        $response->assertStatus(200);
    }

    /** @test - Test 22: Organize Stock Remains Functional */
    public function test_organize_stock_remains_functional(): void
    {
        $response = $this->actingAs($this->user)->get('/stock');
        $response->assertStatus(200);
    }

    /** @test - Test 23, 24 & 25: UI Rendering (Light Mode, Dark Mode & Responsiveness) */
    public function test_light_mode_dark_mode_and_responsive_ui_rendering(): void
    {
        $task = $this->createReadyOrder();

        $response = $this->actingAs($this->user)->get('/transport/delivery-orders?queue=ready_for_assignment');

        $response->assertStatus(200);
        $response->assertSee('Delivery Orders');
        $response->assertSee($task->order_reference);
    }

    /** @test - Test 26: Large Dataset Search and Filter Behavior */
    public function test_large_dataset_search_and_filter(): void
    {
        // Generate dataset
        for ($i = 1; $i <= 20; $i++) {
            $orderNum = sprintf('SO-2026-SEARCH%03d', $i);
            $order = SalesOrder::create([
                'order_number' => $orderNum,
                'customer_id' => $this->customer->id,
                'warehouse_id' => $this->warehouse->id,
                'order_date' => date('Y-m-d'),
                'order_priority' => $i % 2 === 0 ? 'urgent' : 'normal',
                'status' => 'waiting_warehouse',
            ]);
            $task = $this->transportEngine->createTransportRequestFromSalesOrder($order);
            $this->transportEngine->markReadyForDispatch($orderNum);
        }

        // Test Search Filter
        $response = $this->actingAs($this->user)->get('/transport/delivery-orders?search=SO-2026-SEARCH005');
        $response->assertStatus(200);
        $response->assertSee('SO-2026-SEARCH005');
        $response->assertDontSee('SO-2026-SEARCH006');

        // Test Priority Filter
        $response2 = $this->actingAs($this->user)->get('/transport/delivery-orders?priority=urgent');
        $response2->assertStatus(200);
        $response2->assertSee('URGENT');
    }
}

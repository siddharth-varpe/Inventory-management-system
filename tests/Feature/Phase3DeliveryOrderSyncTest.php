<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Customer;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\TransportRequest;
use App\Models\PickingTask;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Domain\Sales\SalesOrderService;
use App\Domain\Transport\TransportManagementEngine;
use App\Domain\Transport\TransportPlanningEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class Phase3DeliveryOrderSyncTest extends TestCase
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
            'code' => 'WH01',
            'name' => 'Main Distribution Center',
            'city' => 'Mumbai',
        ]);

        $this->transportEngine = app(TransportManagementEngine::class);
        $this->planningEngine = app(TransportPlanningEngine::class);
    }

    /** @test */
    public function test_crm_sales_order_creation_syncs_transport_task_with_awaiting_warehouse_status(): void
    {
        $order = SalesOrder::create([
            'order_number' => 'SO-2026-000101',
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'order_date' => date('Y-m-d'),
            'expected_dispatch_date' => date('Y-m-d', strtotime('+2 days')),
            'order_priority' => 'high',
            'status' => 'waiting_warehouse',
            'subtotal' => 10000.00,
            'grand_total' => 11800.00,
            'delivery_address' => 'Plot 45, MIDC Industrial Area, Andheri East, Mumbai',
        ]);

        $task = $this->transportEngine->createTransportRequestFromSalesOrder($order);

        $this->assertNotNull($task);
        $this->assertEquals('SO-2026-000101', $task->order_reference);
        $this->assertEquals('awaiting_warehouse', $task->status);
        $this->assertEquals('Awaiting Warehouse', $task->status_label);
        $this->assertNull($task->warehouse_completed_at);
    }

    /** @test */
    public function test_essential_delivery_information_is_synchronized(): void
    {
        $order = SalesOrder::create([
            'order_number' => 'SO-2026-000102',
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'order_date' => date('Y-m-d'),
            'order_priority' => 'urgent',
            'status' => 'waiting_warehouse',
            'delivery_address' => 'Warehouse 12, Logistics Park, Pune',
        ]);

        $task = $this->transportEngine->createTransportRequestFromSalesOrder($order);

        $this->assertEquals('SO-2026-000102', $task->order_reference);
        $this->assertEquals('Acme Logistics Supermarket', $task->customer_name);
        $this->assertEquals('Warehouse 12, Logistics Park, Pune', $task->delivery_address);
        $this->assertEquals('Pune', $task->city);
        $this->assertEquals('urgent', $task->priority);
        $this->assertEquals('CRM Sales Order', $task->source_module);
    }

    /** @test */
    public function test_confidential_crm_financials_are_not_exposed(): void
    {
        $order = SalesOrder::create([
            'order_number' => 'SO-2026-000103',
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'order_date' => date('Y-m-d'),
            'status' => 'waiting_warehouse',
            'subtotal' => 50000.00,
            'order_discount_amount' => 5000.00,
            'grand_total' => 53100.00,
        ]);

        $task = $this->transportEngine->createTransportRequestFromSalesOrder($order);

        $response = $this->actingAs($this->user)->getJson("/transport/delivery-orders/{$task->id}");
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertArrayNotHasKey('profit', $data);
        $this->assertArrayNotHasKey('margin', $data);
        $this->assertArrayNotHasKey('credit_limit', $data);
        $this->assertArrayNotHasKey('subtotal', $data);
        $this->assertArrayNotHasKey('grand_total', $data);
    }

    /** @test */
    public function test_resource_assignment_is_locked_when_awaiting_warehouse(): void
    {
        $order = SalesOrder::create([
            'order_number' => 'SO-2026-000104',
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'order_date' => date('Y-m-d'),
            'status' => 'waiting_warehouse',
        ]);

        $task = $this->transportEngine->createTransportRequestFromSalesOrder($order);

        $driver = Driver::create([
            'driver_code' => 'DRV-999001',
            'employee_id' => 'EMP-999001',
            'driver_name' => 'Test Driver',
            'mobile_number' => '9999988888',
            'license_number' => 'DL999001',
            'license_expiry' => date('Y-m-d', strtotime('+2 years')),
            'status' => 'available',
        ]);

        $vehicle = Vehicle::create([
            'vehicle_code' => 'VEH-999001',
            'vehicle_number' => 'MH12LOCK1',
            'vehicle_type' => 'Van',
            'load_capacity_kg' => 1000,
            'status' => 'available',
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/AWAITING WAREHOUSE/');

        $this->planningEngine->assignVehicle($task, $vehicle->id, $this->user->id);
    }

    /** @test */
    public function test_warehouse_pick_and_pack_in_progress_keeps_task_in_awaiting_warehouse(): void
    {
        $order = SalesOrder::create([
            'order_number' => 'SO-2026-000105',
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'order_date' => date('Y-m-d'),
            'status' => 'waiting_warehouse',
        ]);

        $task = $this->transportEngine->createTransportRequestFromSalesOrder($order);

        $pickingTask = PickingTask::create([
            'task_number' => 'PICK-00105',
            'order_reference' => 'SO-2026-000105',
            'customer_name' => $this->customer->company_name,
            'picking_type' => 'single',
            'priority' => 'normal',
            'warehouse_id' => $this->warehouse->id,
            'status' => 'picking',
        ]);

        $task->refresh();
        $this->assertEquals('awaiting_warehouse', $task->status);
        $this->assertNull($task->warehouse_completed_at);
    }

    /** @test */
    public function test_warehouse_completion_automatically_transitions_task_to_ready_for_assignment(): void
    {
        $order = SalesOrder::create([
            'order_number' => 'SO-2026-000106',
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'order_date' => date('Y-m-d'),
            'status' => 'waiting_warehouse',
        ]);

        $task = $this->transportEngine->createTransportRequestFromSalesOrder($order);
        $this->assertEquals('awaiting_warehouse', $task->status);

        $updatedTask = $this->transportEngine->markReadyForDispatch('SO-2026-000106', [
            'package_count' => 3,
            'package_type' => 'Sealed Carton',
            'weight_kg' => 12.5,
        ]);

        $this->assertNotNull($updatedTask);
        $this->assertEquals('ready_for_assignment', $updatedTask->status);
        $this->assertEquals('Ready for Assignment', $updatedTask->status_label);
        $this->assertNotNull($updatedTask->warehouse_completed_at);
        $this->assertEquals(3, $updatedTask->package_count);
    }

    /** @test */
    public function test_duplicate_synchronization_does_not_create_multiple_transport_tasks(): void
    {
        $order = SalesOrder::create([
            'order_number' => 'SO-2026-000107',
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'order_date' => date('Y-m-d'),
            'status' => 'waiting_warehouse',
        ]);

        $task1 = $this->transportEngine->createTransportRequestFromSalesOrder($order);
        $task2 = $this->transportEngine->createTransportRequestFromSalesOrder($order);

        $this->assertEquals($task1->id, $task2->id);
        $this->assertEquals(1, TransportRequest::where('order_reference', 'SO-2026-000107')->count());
    }

    /** @test */
    public function test_enterprise_order_id_remains_immutable(): void
    {
        $order = SalesOrder::create([
            'order_number' => 'SO-2026-000108',
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'order_date' => date('Y-m-d'),
            'status' => 'waiting_warehouse',
        ]);

        $task = $this->transportEngine->createTransportRequestFromSalesOrder($order);

        $this->assertEquals('SO-2026-000108', $task->order_reference);
        $this->assertEquals($order->id, $task->sales_order_id);
    }

    /** @test */
    public function test_sales_order_cancellation_syncs_cancelled_status(): void
    {
        $order = SalesOrder::create([
            'order_number' => 'SO-2026-000109',
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'order_date' => date('Y-m-d'),
            'status' => 'waiting_warehouse',
        ]);

        $task = $this->transportEngine->createTransportRequestFromSalesOrder($order);

        $cancelledTask = $this->transportEngine->syncOrderCancellation($order, 'Customer requested cancellation');

        $this->assertEquals('cancelled', $cancelledTask->status);
        $this->assertEquals('Cancelled', $cancelledTask->status_label);
        $this->assertStringContainsString('Customer requested cancellation', $cancelledTask->delivery_failure_reason);
    }

    /** @test */
    public function test_delivery_orders_workspace_search_and_filters(): void
    {
        $this->withoutExceptionHandling();
        $order1 = SalesOrder::create([
            'order_number' => 'SO-2026-000110',
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'order_date' => date('Y-m-d'),
            'order_priority' => 'urgent',
            'status' => 'waiting_warehouse',
        ]);
        $task1 = $this->transportEngine->createTransportRequestFromSalesOrder($order1);

        $order2 = SalesOrder::create([
            'order_number' => 'SO-2026-000111',
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'order_date' => date('Y-m-d'),
            'order_priority' => 'low',
            'status' => 'waiting_warehouse',
        ]);
        $task2 = $this->transportEngine->createTransportRequestFromSalesOrder($order2);
        $this->transportEngine->markReadyForDispatch('SO-2026-000111');

        $response = $this->actingAs($this->user)->get('/transport/delivery-orders?queue=awaiting_warehouse');
        $response->assertStatus(200);
        $response->assertSee('SO-2026-000110');
        $response->assertDontSee('SO-2026-000111');

        $response2 = $this->actingAs($this->user)->get('/transport/delivery-orders?queue=ready_for_assignment');
        $response2->assertStatus(200);
        $response2->assertSee('SO-2026-000111');
        $response2->assertDontSee('SO-2026-000110');
    }

    /** @test */
    public function test_delivery_order_profile_json_endpoint(): void
    {
        $order = SalesOrder::create([
            'order_number' => 'SO-2026-000112',
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'order_date' => date('Y-m-d'),
            'order_priority' => 'high',
            'status' => 'waiting_warehouse',
        ]);

        $task = $this->transportEngine->createTransportRequestFromSalesOrder($order);

        $response = $this->actingAs($this->user)->getJson("/transport/delivery-orders/{$task->id}");
        $response->assertStatus(200);
        $response->assertJson([
            'id' => $task->id,
            'order_reference' => 'SO-2026-000112',
            'status' => 'awaiting_warehouse',
            'status_label' => 'Awaiting Warehouse',
        ]);
        $response->assertJsonStructure([
            'id', 'request_number', 'order_reference', 'customer_name', 'delivery_address',
            'delivery_city', 'priority', 'status', 'status_label', 'status_badge_class',
            'warehouse_status_label', 'warehouse_status_badge_class', 'timeline'
        ]);
    }
}

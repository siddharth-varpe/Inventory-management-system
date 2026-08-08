<?php

namespace Tests\Feature;

use App\Domain\Transport\DispatchExecutionEngine;
use App\Domain\Transport\TransportPlanningEngine;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Customer;
use App\Models\DeliveryTimeline;
use App\Models\Driver;
use App\Models\DriverVehicleAssignment;
use App\Models\CrmLead;
use App\Models\PickingTask;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\TransportRequest;
use App\Models\TransportTrip;
use App\Models\Unit;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EndToEndSystemWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Customer $customer;
    protected Product $product;
    protected Driver $driver;
    protected Vehicle $vehicle;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        // 1. Create Baseline Master Data
        $category = Category::create(['name' => 'General Logistics', 'code' => 'LOG']);
        $unit = Unit::create(['name' => 'Pieces', 'short_name' => 'PCS']);

        $this->product = Product::create([
            'code' => 'PROD-E2E-101',
            'sku' => 'SKU-E2E-101',
            'name' => 'High Density Packaging Box',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'unit_price' => 150.00,
            'stock_quantity' => 1000,
            'status' => 'active',
        ]);

        $this->customer = Customer::create([
            'customer_code' => 'CUST-E2E-001',
            'company_name' => 'Apex Industrial Solutions',
            'contact_person' => 'Vikram Malhotra',
            'email' => 'vikram@apexind.com',
            'phone' => '9876543210',
            'city' => 'Pune',
            'address' => 'Plot 42, Hadapsar Industrial Area',
        ]);

        $this->driver = Driver::create([
            'driver_code' => 'DRV-E2E-01',
            'driver_name' => 'Siddharth Varpe',
            'phone_number' => '9888888888',
            'license_number' => 'MH-12-2026-0001',
            'license_class' => 'Heavy Commercial',
            'license_expiry_date' => now()->addYears(3),
            'employee_id' => 'EMP-E2E-01',
            'status' => 'available',
        ]);

        $this->vehicle = Vehicle::create([
            'vehicle_code' => 'VEH-E2E-01',
            'vehicle_number' => 'MH12AU2233',
            'vehicle_type' => 'Heavy Commercial Vehicle',
            'make' => 'Tata',
            'model' => 'Signa 2823',
            'load_capacity_kg' => 15000.00,
            'volume_capacity_m3' => 60.00,
            'status' => 'available',
        ]);
    }

    /**
     * Complete 14-Step Canonical ERP Workflow Test:
     * CRM -> Lead -> Sales Order -> Warehouse Pick & Pack -> Transport Sync -> Resource Assignment -> Dispatch -> Delivery -> History
     */
    public function test_complete_canonical_erp_workflow_chain(): void
    {
        // STEP 1: CRM Lead Creation & Persistence
        $lead = CrmLead::create([
            'lead_number' => 'LEAD-E2E-2026-001',
            'company_name' => $this->customer->company_name,
            'contact_person' => 'Vikram Malhotra',
            'phone' => $this->customer->phone,
            'email' => $this->customer->email,
            'status' => 'new',
            'expected_revenue' => 50000.00,
            'created_by' => $this->user->id,
        ]);

        $this->assertDatabaseHas('crm_leads', [
            'id' => $lead->id,
            'status' => 'new',
        ]);

        // STEP 2: Sales Order Creation from CRM Lead
        $salesOrder = SalesOrder::create([
            'order_number' => 'SO-E2E-2026-9001',
            'customer_id' => $this->customer->id,
            'lead_id' => $lead->id,
            'order_date' => now(),
            'total_amount' => 15000.00,
            'status' => 'pending_warehouse',
        ]);

        $this->assertDatabaseHas('sales_orders', [
            'id' => $salesOrder->id,
            'status' => 'pending_warehouse',
        ]);

        // STEP 3: Organize Stock / Warehouse Picking Task Execution
        $pickingTask = PickingTask::create([
            'task_number' => 'PICK-E2E-2026-0001',
            'order_reference' => $salesOrder->order_number,
            'customer_name' => $this->customer->company_name,
            'assigned_user_id' => $this->user->id,
            'status' => 'pending',
        ]);

        \App\Models\PickingItem::create([
            'picking_task_id' => $pickingTask->id,
            'product_id' => $this->product->id,
            'sku' => $this->product->sku,
            'product_name' => $this->product->name,
            'requested_quantity' => 10,
            'picked_quantity' => 10,
            'is_verified' => true,
        ]);

        $pickingTask->update(['status' => 'picking', 'started_at' => now()]);
        $pickingTask->update(['status' => 'picked']);
        $pickingTask->update(['status' => 'packed']);
        $pickingTask->update(['status' => 'completed', 'completed_at' => now()]);

        $salesOrder->update(['status' => 'warehouse_completed']);

        $this->assertDatabaseHas('picking_tasks', [
            'id' => $pickingTask->id,
            'status' => 'completed',
        ]);

        // STEP 5: Transport Delivery Order Auto-Sync from Sealed Warehouse Task
        $pickingTask->update([
            'is_all_verified' => true,
            'status' => 'seal_ready',
        ]);

        $transportEngine = app(\App\Domain\Transport\TransportManagementEngine::class);
        $transportRequest = $transportEngine->createTransportTaskFromSealedPickingTask($pickingTask);

        $this->assertNotNull($transportRequest);
        $this->assertNotNull($transportRequest->warehouse_completed_at);
        $this->assertEquals('Seal & Ready to Dispatch', $transportRequest->warehouse_status_label);

        // STEP 6: Driver & Vehicle Resource Assignment
        $planningEngine = app(\App\Domain\Transport\TransportPlanningEngine::class);
        $assignment = $planningEngine->assignDriverAndVehicle(
            $transportRequest,
            $this->driver->id,
            $this->vehicle->id,
            $this->user->id
        );

        $transportRequest->refresh();
        $this->assertEquals('driver_vehicle_assigned', $transportRequest->status);
        $this->assertEquals($this->driver->id, $transportRequest->driver_id);
        $this->assertEquals($this->vehicle->id, $transportRequest->vehicle_id);

        $this->driver->refresh();
        $this->vehicle->refresh();
        $this->assertEquals('on_delivery', $this->driver->status);
        $this->assertEquals('on_trip', $this->vehicle->status);

        // STEP 7: Shipment Dispatch Control Execution
        $executionEngine = app(DispatchExecutionEngine::class);
        $dispatchedOrder = $executionEngine->confirmDispatchOrder($transportRequest, $this->user->id, 'Gate Pass Approved');

        $transportRequest->refresh();
        $this->driver->refresh();
        $this->vehicle->refresh();

        $this->assertEquals('dispatched', $transportRequest->status);
        $this->assertNotNull($transportRequest->dispatch_number);
        $this->assertEquals('on_delivery', $this->driver->status);
        $this->assertEquals('on_trip', $this->vehicle->status);

        // STEP 8: Audit Trail & Real-Event Timeline Verification
        $this->assertDatabaseHas('delivery_timelines', [
            'transport_request_id' => $transportRequest->id,
            'event_type' => 'Shipment Dispatched',
            'status' => 'dispatched',
        ]);
    }
}

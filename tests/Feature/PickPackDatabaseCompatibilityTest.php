<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\PickingItem;
use App\Models\PickingTask;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PickPackDatabaseCompatibilityTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->customer = Customer::create([
            'customer_code' => 'CUST-TEST-001',
            'company_name' => 'Test Customer Corp',
            'status' => 'active',
        ]);
    }

    /** @test */
    public function test_1_zero_tasks_page_loads_cleanly_without_exception(): void
    {
        $response = $this->actingAs($this->user)->get(route('organize.fulfillment.index'));
        $response->assertStatus(200);
        $response->assertSee('No tasks found in this queue.');
    }

    /** @test */
    public function test_2_single_task_loads_successfully(): void
    {
        $task = PickingTask::create([
            'task_number' => 'PICK-2026-0001',
            'order_reference' => 'SO-10001',
            'customer_name' => 'Acme Global',
            'priority' => 'high',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user)->get(route('organize.fulfillment.index'));
        $response->assertStatus(200);
        $response->assertSee('PICK-2026-0001');
    }

    /** @test */
    public function test_3_priority_ordering_urgent_high_medium_low(): void
    {
        $low = PickingTask::create(['task_number' => 'PICK-LOW', 'order_reference' => 'SO-1', 'priority' => 'low', 'status' => 'pending']);
        $urgent = PickingTask::create(['task_number' => 'PICK-URGENT', 'order_reference' => 'SO-2', 'priority' => 'urgent', 'status' => 'pending']);
        $medium = PickingTask::create(['task_number' => 'PICK-MED', 'order_reference' => 'SO-3', 'priority' => 'medium', 'status' => 'pending']);
        $high = PickingTask::create(['task_number' => 'PICK-HIGH', 'order_reference' => 'SO-4', 'priority' => 'high', 'status' => 'pending']);

        $tasks = PickingTask::whereIn('status', ['pending', 'assigned', 'picking', 'picked', 'packed'])
            ->orderByPriorityAndFifo()
            ->pluck('task_number')
            ->toArray();

        $this->assertEquals(['PICK-URGENT', 'PICK-HIGH', 'PICK-MED', 'PICK-LOW'], $tasks);
    }

    /** @test */
    public function test_4_fifo_ordering_oldest_task_first_for_same_priority(): void
    {
        $older = PickingTask::create([
            'task_number' => 'PICK-OLD',
            'order_reference' => 'SO-101',
            'priority' => 'high',
            'status' => 'pending',
            'created_at' => now()->subHours(2),
        ]);

        $newer = PickingTask::create([
            'task_number' => 'PICK-NEW',
            'order_reference' => 'SO-102',
            'priority' => 'high',
            'status' => 'pending',
            'created_at' => now()->subHour(),
        ]);

        $tasks = PickingTask::orderByPriorityAndFifo()->pluck('task_number')->toArray();
        $this->assertEquals(['PICK-OLD', 'PICK-NEW'], $tasks);
    }

    /** @test */
    public function test_5_unknown_priorities_do_not_crash_and_rank_after_known_priorities(): void
    {
        $known = PickingTask::create(['task_number' => 'PICK-KNOWN', 'order_reference' => 'SO-1', 'priority' => 'low', 'status' => 'pending']);
        $unknownPriority = PickingTask::create(['task_number' => 'PICK-UNK', 'order_reference' => 'SO-3', 'priority' => 'custom_prio', 'status' => 'pending']);

        $response = $this->actingAs($this->user)->get(route('organize.fulfillment.index'));
        $response->assertStatus(200);

        $ordered = PickingTask::orderByPriorityAndFifo()->pluck('task_number')->toArray();
        $this->assertEquals('PICK-KNOWN', $ordered[0]);
    }

    /** @test */
    public function test_6_search_by_task_number(): void
    {
        PickingTask::create(['task_number' => 'PICK-MATCH-99', 'order_reference' => 'SO-1', 'priority' => 'high', 'status' => 'pending']);
        PickingTask::create(['task_number' => 'PICK-OTHER-11', 'order_reference' => 'SO-2', 'priority' => 'high', 'status' => 'pending']);

        $response = $this->actingAs($this->user)->get(route('organize.fulfillment.index', ['search' => 'MATCH-99']));
        $response->assertStatus(200);
        $response->assertSee('PICK-MATCH-99');
        $response->assertDontSee('PICK-OTHER-11');
    }

    /** @test */
    public function test_7_search_by_order_reference(): void
    {
        PickingTask::create(['task_number' => 'PICK-1', 'order_reference' => 'SO-TARGET-77', 'priority' => 'high', 'status' => 'pending']);
        PickingTask::create(['task_number' => 'PICK-2', 'order_reference' => 'SO-OTHER-88', 'priority' => 'high', 'status' => 'pending']);

        $response = $this->actingAs($this->user)->get(route('organize.fulfillment.index', ['search' => 'TARGET-77']));
        $response->assertStatus(200);
        $response->assertSee('SO-TARGET-77');
    }

    /** @test */
    public function test_8_search_by_customer_name(): void
    {
        PickingTask::create(['task_number' => 'PICK-1', 'order_reference' => 'SO-1', 'customer_name' => 'Apex Logistics Corp', 'priority' => 'high', 'status' => 'pending']);

        $response = $this->actingAs($this->user)->get(route('organize.fulfillment.index', ['search' => 'Apex Logistics']));
        $response->assertStatus(200);
        $response->assertSee('Apex Logistics Corp');
    }

    /** @test */
    public function test_9_pagination_works(): void
    {
        for ($i = 1; $i <= 20; $i++) {
            PickingTask::create([
                'task_number' => "PICK-PAG-{$i}",
                'order_reference' => "SO-PAG-{$i}",
                'priority' => 'medium',
                'status' => 'pending',
            ]);
        }

        $response = $this->actingAs($this->user)->get(route('organize.fulfillment.index'));
        $response->assertStatus(200);
        $tasks = $response->viewData('tasks');
        $this->assertTrue($tasks->hasPages());
        $this->assertEquals(20, $tasks->total());
    }

    /** @test */
    public function test_10_invalid_selected_task_id_does_not_crash(): void
    {
        PickingTask::create(['task_number' => 'PICK-VALID', 'order_reference' => 'SO-1', 'priority' => 'high', 'status' => 'pending']);

        $response = $this->actingAs($this->user)->get(route('organize.fulfillment.index', ['task_id' => 999999]));
        $response->assertStatus(200);
        $response->assertSee('PICK-VALID');
    }

    /** @test */
    public function test_11_completed_selected_task_remains_completed(): void
    {
        $completedTask = PickingTask::create([
            'task_number' => 'PICK-DONE-1',
            'order_reference' => 'SO-DONE-1',
            'priority' => 'high',
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $response = $this->actingAs($this->user)->get(route('organize.fulfillment.index', ['task_id' => $completedTask->id]));
        $response->assertStatus(200);

        $this->assertEquals('completed', $completedTask->fresh()->status);
    }

    /** @test */
    public function test_12_clicking_another_task_does_not_reopen_completed_task(): void
    {
        $completedTask = PickingTask::create(['task_number' => 'PICK-C1', 'order_reference' => 'SO-C1', 'status' => 'completed']);
        $activeTask = PickingTask::create(['task_number' => 'PICK-A1', 'order_reference' => 'SO-A1', 'status' => 'pending']);

        $this->actingAs($this->user)->get(route('organize.fulfillment.index', ['task_id' => $activeTask->id]));

        $this->assertEquals('completed', $completedTask->fresh()->status);
        $this->assertEquals('picking', $activeTask->fresh()->status);
    }

    /** @test */
    public function test_13_seal_and_ready_for_dispatch_transitions_state(): void
    {
        $order = SalesOrder::create([
            'order_number' => 'SO-SEAL',
            'order_date' => now(),
            'customer_id' => $this->customer->id,
            'customer_name' => 'Seal Customer',
            'status' => 'warehouse_in_progress',
            'total_amount' => 100.00,
        ]);

        $product = Product::create([
            'name' => 'Seal Test Item',
            'code' => 'PRD-SEAL-01',
            'sku' => 'SKU-SEAL-01',
            'purchase_price' => 10,
            'cost_price' => 10,
            'selling_price' => 20,
            'physical_stock' => 50,
            'reserved_stock' => 10,
        ]);
        $task = PickingTask::create(['task_number' => 'PICK-SEAL', 'order_reference' => 'SO-SEAL', 'status' => 'picked']);
        PickingItem::create([
            'picking_task_id' => $task->id,
            'product_id' => $product->id,
            'requested_quantity' => 5,
            'picked_quantity' => 5,
            'is_verified' => true,
        ]);

        $payload = [
            'package_type' => 'Box',
            'weight_kg' => 12.5,
            'package_count' => 2,
        ];

        $response = $this->actingAs($this->user)->post(route('organize.fulfillment.seal-ready', $task->id), $payload);
        $response->assertStatus(302);

        $this->assertEquals('completed', $task->fresh()->status);
        $this->assertEquals('ready_for_dispatch', $order->fresh()->status);
    }

    /** @test */
    public function test_14_transport_synchronization_creates_or_updates_transport_request(): void
    {
        $order = SalesOrder::create([
            'order_number' => 'SO-SYNC-100',
            'order_date' => now(),
            'customer_id' => $this->customer->id,
            'customer_name' => 'Global Logistics Inc',
            'status' => 'warehouse_in_progress',
            'total_amount' => 200.00,
        ]);

        $product = Product::create([
            'name' => 'Sync Test Item',
            'code' => 'PRD-SYNC-01',
            'sku' => 'SKU-SYNC-01',
            'purchase_price' => 10,
            'cost_price' => 10,
            'selling_price' => 20,
            'physical_stock' => 50,
            'reserved_stock' => 10,
        ]);
        $task = PickingTask::create([
            'task_number' => 'PICK-SYNC',
            'order_reference' => 'SO-SYNC-100',
            'customer_name' => 'Global Logistics Inc',
            'status' => 'picked',
        ]);
        PickingItem::create([
            'picking_task_id' => $task->id,
            'product_id' => $product->id,
            'requested_quantity' => 10,
            'picked_quantity' => 10,
            'is_verified' => true,
        ]);

        $this->actingAs($this->user)->post(route('organize.fulfillment.seal-ready', $task->id), [
            'package_type' => 'Crate',
            'weight_kg' => 25.0,
            'package_count' => 1,
        ]);

        $this->assertDatabaseHas('transport_requests', [
            'order_reference' => 'SO-SYNC-100',
        ]);
    }

    /** @test */
    public function test_15_double_click_seal_action_handled_safely(): void
    {
        $order = SalesOrder::create([
            'order_number' => 'SO-DOUBLE',
            'order_date' => now(),
            'customer_id' => $this->customer->id,
            'customer_name' => 'Double Click Customer',
            'status' => 'warehouse_in_progress',
            'total_amount' => 100.00,
        ]);

        $product = Product::create([
            'name' => 'Double Test Item',
            'code' => 'PRD-DBL-01',
            'sku' => 'SKU-DBL-01',
            'purchase_price' => 10,
            'cost_price' => 10,
            'selling_price' => 20,
            'physical_stock' => 50,
            'reserved_stock' => 10,
        ]);
        $task = PickingTask::create(['task_number' => 'PICK-DBL', 'order_reference' => 'SO-DOUBLE', 'status' => 'picked']);
        PickingItem::create([
            'picking_task_id' => $task->id,
            'product_id' => $product->id,
            'requested_quantity' => 5,
            'picked_quantity' => 5,
            'is_verified' => true,
        ]);

        $payload = [
            'package_type' => 'Box',
            'weight_kg' => 5.0,
        ];

        // First click -> Success
        $firstResponse = $this->actingAs($this->user)->post(route('organize.fulfillment.seal-ready', $task->id), $payload);
        $firstResponse->assertStatus(302);

        // Second click on already completed task -> Graceful error redirect back, no crash
        $secondResponse = $this->actingAs($this->user)->post(route('organize.fulfillment.seal-ready', $task->id), $payload);
        $secondResponse->assertStatus(302);
        $secondResponse->assertSessionHas('error');
    }
}

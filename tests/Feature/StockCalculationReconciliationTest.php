<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockCalculationReconciliationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->category = Category::create([
            'name' => 'General Electronics',
            'code' => 'CAT-ELEC',
            'status' => 'active',
        ]);
    }

    /** @test */
    public function test_available_stock_is_automatically_calculated_on_product_save(): void
    {
        $product = Product::create([
            'name' => 'Smart Thermal Sensor',
            'sku' => 'SKU-TEST-TH01',
            'code' => 'PRD-TH01',
            'category_id' => $this->category->id,
            'status' => 'active',
            'physical_stock' => 50,
            'reserved_stock' => 10,
            'reorder_level' => 15,
            'created_by' => $this->user->id,
        ]);

        $product->refresh();

        $this->assertEquals(40, $product->available_stock);
        $this->assertEquals('normal', $product->stock_status);
    }

    /** @test */
    public function test_product_catalog_and_stock_dashboard_out_of_stock_metrics_reconcile(): void
    {
        // 1. Product In Stock
        Product::create([
            'name' => 'In Stock Item',
            'sku' => 'SKU-INSTOCK-01',
            'code' => 'PRD-IS01',
            'category_id' => $this->category->id,
            'physical_stock' => 100,
            'reserved_stock' => 10,
            'reorder_level' => 20,
            'created_by' => $this->user->id,
        ]);

        // 2. Product Low Stock
        Product::create([
            'name' => 'Low Stock Item',
            'sku' => 'SKU-LOWSTOCK-01',
            'code' => 'PRD-LS01',
            'category_id' => $this->category->id,
            'physical_stock' => 15,
            'reserved_stock' => 5,
            'reorder_level' => 20,
            'created_by' => $this->user->id,
        ]);

        // 3. Product Out of Stock (Available = 0)
        Product::create([
            'name' => 'Out of Stock Item',
            'sku' => 'SKU-OUTSTOCK-01',
            'code' => 'PRD-OS01',
            'category_id' => $this->category->id,
            'physical_stock' => 5,
            'reserved_stock' => 5,
            'reorder_level' => 10,
            'created_by' => $this->user->id,
        ]);

        // Dashboard Metrics
        $dashboardResponse = $this->get('/stock');
        $dashboardResponse->assertStatus(200);
        $dashboardMetrics = $dashboardResponse->viewData('metrics');

        // Product Scopes
        $scopeOutOfStockCount = Product::outOfStock()->count();
        $scopeLowStockCount = Product::lowStock()->count();

        // Assert Exact Parity
        $this->assertEquals(1, $dashboardMetrics['out_of_stock']);
        $this->assertEquals(1, $scopeOutOfStockCount);

        $this->assertEquals(1, $dashboardMetrics['low_stock']);
        $this->assertEquals(1, $scopeLowStockCount);
    }

    /** @test */
    public function test_stock_mutation_preserves_mathematical_integrity(): void
    {
        $product = Product::create([
            'name' => 'Adjustable Clamp',
            'sku' => 'SKU-CLAMP-01',
            'code' => 'PRD-CL01',
            'category_id' => $this->category->id,
            'physical_stock' => 20,
            'reserved_stock' => 0,
            'reorder_level' => 5,
            'created_by' => $this->user->id,
        ]);

        $this->assertEquals(20, $product->available_stock);

        // Reserve 8 units
        $product->update([
            'reserved_stock' => 8,
        ]);

        $product->refresh();
        $this->assertEquals(12, $product->available_stock);

        // Receive 30 physical units
        $product->update([
            'physical_stock' => $product->physical_stock + 30,
        ]);

        $product->refresh();
        $this->assertEquals(50, $product->physical_stock);
        $this->assertEquals(8, $product->reserved_stock);
        $this->assertEquals(42, $product->available_stock);
    }
}

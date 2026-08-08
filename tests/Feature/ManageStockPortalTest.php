<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\StockReceipt;
use App\Models\User;
use App\Services\Barcode\BarcodeService;
use App\Services\Contracts\ProductServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManageStockPortalTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_can_render_stock_dashboard_for_authenticated_users(): void
    {
        $response = $this->actingAs($this->user)->get(route('stock.dashboard'));
        $response->assertStatus(200);
        $response->assertViewIs('stock.dashboard');
    }

    /** @test */
    public function it_can_create_a_product_master_and_generate_sku_and_code(): void
    {
        $category = Category::create(['name' => 'Hardware', 'slug' => 'hardware']);

        $productData = [
            'name' => 'Heavy Duty Bolt M12',
            'product_type' => 'single',
            'status' => 'active',
            'category_id' => $category->id,
            'purchase_price' => 50.00,
            'cost_price' => 50.00,
            'selling_price' => 80.00,
            'min_stock' => 10,
            'reorder_level' => 20,
            'max_stock' => 500,
        ];

        $response = $this->actingAs($this->user)->post(route('products.store'), $productData);

        $product = Product::where('name', 'Heavy Duty Bolt M12')->first();
        $this->assertNotNull($product);
        $this->assertStringStartsWith('SKU-', $product->sku);
        $this->assertStringStartsWith('PRD-', $product->code);
        $response->assertRedirect(route('products.show', $product));
    }

    /** @test */
    public function it_can_duplicate_an_existing_product(): void
    {
        $original = Product::create([
            'name' => 'Original Drill Machine',
            'code' => 'PRD-ORIG1',
            'sku' => 'SKU-ORIG1',
            'product_type' => 'single',
            'status' => 'active',
            'purchase_price' => 1000,
            'cost_price' => 1000,
            'selling_price' => 1500,
        ]);

        $response = $this->actingAs($this->user)->post(route('products.duplicate', $original));
        $response->assertStatus(302);

        $duplicate = Product::where('name', 'Original Drill Machine (Copy)')->first();
        $this->assertNotNull($duplicate);
        $this->assertNotEquals($original->sku, $duplicate->sku);
    }

    /** @test */
    public function it_can_receive_supplier_stock_and_update_weighted_average_cost(): void
    {
        $product = Product::create([
            'name' => 'Copper Cable Reel',
            'code' => 'PRD-CABLE1',
            'sku' => 'SKU-CABLE1',
            'product_type' => 'single',
            'status' => 'active',
            'purchase_price' => 100,
            'cost_price' => 100,
            'selling_price' => 150,
            'physical_stock' => 10, // 10 units @ 100 = 1000 total cost
        ]);

        $receiveData = [
            'product_id' => $product->id,
            'supplier_name' => 'Global Metal Suppliers',
            'quantity' => 10, // 10 units @ 200 = 2000 total cost
            'unit_cost' => 200,
            'batch_number' => 'BATCH-CABLE-01',
        ];

        $response = $this->actingAs($this->user)->post(route('stock.receive.store'), $receiveData);
        $response->assertStatus(302);

        $product->refresh();
        $this->assertEquals(20, $product->physical_stock);
        // Weighted Average Cost = (10*100 + 10*200) / 20 = 150
        $this->assertEquals(150.00, $product->cost_price);

        $this->assertDatabaseHas('stock_receipts', [
            'product_id' => $product->id,
            'quantity' => 10,
            'unit_cost' => 200,
        ]);
    }

    /** @test */
    public function it_triggers_pending_status_for_high_value_stock_adjustments(): void
    {
        $product = Product::create([
            'name' => 'High Value Generator Engine',
            'code' => 'PRD-GEN01',
            'sku' => 'SKU-GEN01',
            'product_type' => 'single',
            'status' => 'active',
            'cost_price' => 60000,
            'physical_stock' => 5,
        ]);

        // Adjustment of 1 unit loss @ 60,000 > 50,000 threshold
        $adjData = [
            'product_id' => $product->id,
            'type' => 'damaged',
            'quantity' => -1,
            'reason' => 'Crushed in transport',
        ];

        $this->actingAs($this->user)->post(route('stock.adjustments.store'), $adjData);

        $adjustment = StockAdjustment::where('product_id', $product->id)->first();
        $this->assertNotNull($adjustment);
        $this->assertEquals('pending', $adjustment->status);

        // Stock should remain 5 until approved
        $product->refresh();
        $this->assertEquals(5, $product->physical_stock);

        // Approve adjustment
        $this->actingAs($this->user)->post(route('stock.adjustments.approve', $adjustment->id));

        $adjustment->refresh();
        $product->refresh();
        $this->assertEquals('approved', $adjustment->status);
        $this->assertEquals(4, $product->physical_stock);
    }

    /** @test */
    public function it_can_generate_code128_barcode_html(): void
    {
        $barcodeService = new BarcodeService();
        $svg = $barcodeService->generateBarcodeHTML('SKU-TEST-123', 2, 50);

        $this->assertStringContainsString('<svg', $svg);
        $this->assertStringContainsString('rect', $svg);
    }
}

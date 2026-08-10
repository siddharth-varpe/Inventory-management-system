<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\Customer;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\Contracts\ProductServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComprehensiveInventoryReconciliationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Customer $customer;
    protected Warehouse $warehouse;
    protected ProductServiceInterface $productService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['email' => 'auditor@stockmanager.com']);
        $this->actingAs($this->user);

        $this->customer = Customer::create([
            'customer_code' => 'CUST-AUD-01',
            'company_name' => 'Global Audit Corp',
            'contact_person' => 'Alice Cooper',
            'email' => 'alice@auditcorp.com',
            'phone' => '9112233445',
            'status' => 'active',
        ]);

        $this->warehouse = Warehouse::create([
            'name' => 'Main Distribution Hub',
            'code' => 'WH-MAIN',
            'city' => 'Mumbai',
            'status' => 'active',
        ]);

        $this->productService = $this->app->make(ProductServiceInterface::class);
    }

    /** @test */
    public function test_all_products_reconcile_mathematically_and_across_all_modules(): void
    {
        // 1. Create master products with specific physical and reserved quantities
        $mx3s = Product::create([
            'code' => 'PRD-MX3S',
            'sku' => 'SKU-LOGI-MX3S',
            'name' => 'MX Master 3S Wireless Performance Mouse',
            'physical_stock' => 10,
            'reserved_stock' => 10, // Available = 0 -> Out of Stock
            'selling_price' => 8999.00,
            'status' => 'active',
        ]);

        $dell = Product::create([
            'code' => 'PRD-DELL27',
            'sku' => 'SKU-DELL-U2723QE',
            'name' => 'Dell UltraSharp 27 4K USB-C Hub Monitor',
            'physical_stock' => 3,
            'reserved_stock' => 2, // Available = 1 -> Low Stock
            'reorder_level' => 5,
            'selling_price' => 49999.00,
            'status' => 'active',
        ]);

        $sensor = Product::create([
            'code' => 'PRD-SENS-001',
            'sku' => 'SKU-SENS-001',
            'name' => 'Digital Pressure Sensor 24V',
            'physical_stock' => 45,
            'reserved_stock' => 0, // Available = 45 -> In Stock
            'selling_price' => 1899.00,
            'status' => 'active',
        ]);

        // Assert Model Accessors Return Dynamic Calculated Available Stock
        $this->assertEquals(0, $mx3s->available_stock);
        $this->assertEquals('out_of_stock', $mx3s->stock_status);

        $this->assertEquals(1, $dell->available_stock);
        $this->assertEquals('low', $dell->stock_status);

        $this->assertEquals(45, $sensor->available_stock);
        $this->assertEquals('normal', $sensor->stock_status);

        // Assert Stock Dashboard KPIs reconcile 100%
        $this->assertEquals(1, Product::outOfStock()->count());
        $this->assertEquals(1, Product::lowStock()->count());

        // Assert Sales & CRM search API returns exact available stock
        $apiResponse = $this->getJson(route('sales.quotations.search-products'));
        $apiResponse->assertOk();
        $productsApi = collect($apiResponse->json('products'));

        $mx3sApi = $productsApi->firstWhere('sku', 'SKU-LOGI-MX3S');
        $this->assertNotNull($mx3sApi);
        $this->assertEquals(0, $mx3sApi['available_stock']);
        $this->assertTrue($mx3sApi['is_out_of_stock']);

        $dellApi = $productsApi->firstWhere('sku', 'SKU-DELL-U2723QE');
        $this->assertNotNull($dellApi);
        $this->assertEquals(1, $dellApi['available_stock']);
        $this->assertFalse($dellApi['is_out_of_stock']);
    }

    /** @test */
    public function test_stock_mutation_recalculates_available_stock_synchronously(): void
    {
        $product = Product::create([
            'code' => 'PRD-MUT-01',
            'sku' => 'SKU-MUTATION-TEST',
            'name' => 'Industrial Flow Meter',
            'physical_stock' => 100,
            'reserved_stock' => 20,
            'cost_price' => 100.00,
            'status' => 'active',
        ]);

        Inventory::create([
            'product_id' => $product->id,
            'batch_number' => 'LOT-INIT-100',
            'lot_number' => 'LOT-1000',
            'quantity' => 100,
            'unit_cost' => 100.00,
        ]);

        $this->assertEquals(80, $product->available_stock);

        // Perform stock receiving (+50)
        $this->productService->receiveStock([
            'product_id' => $product->id,
            'quantity' => 50,
            'unit_cost' => 100.00,
            'reference_no' => 'RCV-TEST-001',
            'warehouse' => 'WH-MAIN',
        ]);

        $product->refresh();
        $this->assertEquals(150, $product->physical_stock);
        $this->assertEquals(20, $product->reserved_stock);
        $this->assertEquals(130, $product->available_stock);

        // Perform stock adjustment (-30) below threshold
        $this->productService->adjustStock([
            'product_id' => $product->id,
            'quantity' => -30,
            'type' => 'damaged',
            'reference_no' => 'ADJ-TEST-001',
            'notes' => 'Damaged during transfer',
        ]);

        $product->refresh();
        $this->assertEquals(120, $product->physical_stock);
        $this->assertEquals(20, $product->reserved_stock);
        $this->assertEquals(100, $product->available_stock);
    }
}

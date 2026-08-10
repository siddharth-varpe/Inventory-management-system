<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\SalesOrder;
use App\Models\Warehouse;
use App\Services\Contracts\ProductServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCatalogVsSalesReconciliationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Customer $customer;
    protected Warehouse $warehouse;
    protected ProductServiceInterface $productService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['email' => 'admin@stockmanager.com']);
        $this->actingAs($this->user);

        $this->customer = Customer::create([
            'customer_code' => 'CUST-REC-01',
            'company_name' => 'Reconciliation Enterprise Ltd',
            'contact_person' => 'Robert Vance',
            'email' => 'robert@reconciliation.com',
            'phone' => '9988776655',
            'status' => 'active',
        ]);

        $this->warehouse = Warehouse::create([
            'name' => 'Central Hub',
            'code' => 'WH-CENTRAL',
            'city' => 'Delhi',
            'status' => 'active',
        ]);

        $this->productService = $this->app->make(ProductServiceInterface::class);
    }

    /** @test */
    public function test_product_catalog_and_sales_crm_queries_return_100_percent_identical_canonical_products(): void
    {
        // Create products in master products table
        $p1 = Product::create([
            'code' => 'PRD-REC-01',
            'sku' => 'SKU-REC-LOGI',
            'name' => 'MX Master 3S Mouse',
            'physical_stock' => 50,
            'reserved_stock' => 10,
            'available_stock' => 40,
            'selling_price' => 8999.00,
            'status' => 'active',
        ]);

        $p2 = Product::create([
            'code' => 'PRD-REC-02',
            'sku' => 'SKU-REC-DELL',
            'name' => 'Dell 4K Monitor',
            'physical_stock' => 15,
            'reserved_stock' => 5,
            'available_stock' => 10,
            'selling_price' => 49999.00,
            'status' => 'active',
        ]);

        // 1. Fetch Product Catalog Products
        $catalogResponse = $this->get(route('stock.catalog'));
        $catalogResponse->assertOk();

        // 2. Fetch Sales & CRM Workspace Products
        $salesWorkspaceResponse = $this->get(route('sales.workspace'));
        $salesWorkspaceResponse->assertOk();

        // 3. Fetch Sales & CRM Search API Products
        $salesSearchResponse = $this->getJson(route('sales.quotations.search-products', ['q' => 'SKU-REC']));
        $salesSearchResponse->assertOk();

        // 4. Verify canonical data reconciliation across both modules
        $searchData = $salesSearchResponse->json('products');

        $this->assertCount(2, $searchData);

        $matchedP1 = collect($searchData)->firstWhere('sku', 'SKU-REC-LOGI');
        $this->assertNotNull($matchedP1);
        $this->assertEquals($p1->id, $matchedP1['id']);
        $this->assertEquals($p1->sku, $matchedP1['sku']);
        $this->assertEquals($p1->name, $matchedP1['name']);
        $this->assertEquals(8999.00, $matchedP1['selling_price']);
        $this->assertEquals(40, $matchedP1['available_stock']);

        $matchedP2 = collect($searchData)->firstWhere('sku', 'SKU-REC-DELL');
        $this->assertNotNull($matchedP2);
        $this->assertEquals($p2->id, $matchedP2['id']);
        $this->assertEquals($p2->sku, $matchedP2['sku']);
        $this->assertEquals($p2->name, $matchedP2['name']);
        $this->assertEquals(49999.00, $matchedP2['selling_price']);
        $this->assertEquals(10, $matchedP2['available_stock']);
    }

    /** @test */
    public function test_end_to_end_product_identity_preserved_from_catalog_to_quotation_and_sales_order(): void
    {
        $product = Product::create([
            'code' => 'PRD-E2E-01',
            'sku' => 'SKU-E2E-IDENT',
            'name' => 'Enterprise Server Node 1U',
            'physical_stock' => 10,
            'reserved_stock' => 2,
            'available_stock' => 8,
            'selling_price' => 150000.00,
            'status' => 'active',
        ]);

        // Create Quotation with product
        $cartData = json_encode([
            [
                'product_id' => $product->id,
                'quantity' => 2,
                'unit_price' => 150000.00,
            ]
        ]);

        $storeResponse = $this->post(route('sales.quotations.store'), [
            'customer_id' => $this->customer->id,
            'cart_data' => $cartData,
        ]);

        $storeResponse->assertRedirect();
        $quotation = Quotation::where('customer_id', $this->customer->id)->first();
        $this->assertNotNull($quotation);
        $this->assertEquals($product->id, $quotation->items->first()->product_id);

        // Approve quotation
        $this->post(route('sales.quotations.approve', $quotation->id));

        // Convert quotation to Sales Order
        $convertResponse = $this->post(route('sales.quotations.convert', $quotation->id));
        $convertResponse->assertRedirect();

        $salesOrder = SalesOrder::where('customer_id', $this->customer->id)->first();
        $this->assertNotNull($salesOrder);
        $this->assertEquals($product->id, $salesOrder->items->first()->product_id);

        // Assert Product ID is identical throughout
        $this->assertEquals($product->id, $quotation->items->first()->product->id);
        $this->assertEquals($product->id, $salesOrder->items->first()->product->id);
        $this->assertEquals('SKU-E2E-IDENT', $salesOrder->items->first()->product->sku);
    }
}

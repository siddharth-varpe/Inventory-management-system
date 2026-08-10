<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\SalesOrder;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotationManagementAndStockValidationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Customer $customer;
    protected Warehouse $warehouse;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['email' => 'sales@stockmanager.com']);
        $this->actingAs($this->user);

        $this->customer = Customer::create([
            'customer_code' => 'CUST-00001',
            'company_name' => 'Acme Logistics',
            'contact_person' => 'Jane Smith',
            'email' => 'jane@acmelogistics.com',
            'phone' => '9876543210',
            'status' => 'active',
        ]);

        $this->warehouse = Warehouse::create([
            'name' => 'Main Distribution Hub',
            'code' => 'WH-HUB',
            'city' => 'Mumbai',
            'status' => 'active',
        ]);
    }

    /** @test */
    public function test_canonical_product_search_returns_live_available_stock(): void
    {
        Product::create([
            'code' => 'PRD-00010',
            'sku' => 'SKU-LOGI-MX3S',
            'name' => 'MX Master 3S',
            'physical_stock' => 20,
            'reserved_stock' => 5,
            'available_stock' => 15,
            'selling_price' => 8999.00,
            'status' => 'active',
        ]);

        $response = $this->getJson(route('sales.quotations.search-products', ['q' => 'MX3S']));

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('products.0.sku', 'SKU-LOGI-MX3S');
        $response->assertJsonPath('products.0.available_stock', 15);
        $response->assertJsonPath('products.0.is_out_of_stock', false);
    }

    /** @test */
    public function test_adding_out_of_stock_product_to_quotation_is_rejected(): void
    {
        $product = Product::create([
            'code' => 'PRD-00011',
            'sku' => 'SKU-OUT-OF-STOCK',
            'name' => 'Wireless Presenter Remote',
            'physical_stock' => 0,
            'reserved_stock' => 0,
            'available_stock' => 0,
            'selling_price' => 2500.00,
            'status' => 'active',
        ]);

        $cartData = json_encode([
            [
                'product_id' => $product->id,
                'quantity' => 1,
                'unit_price' => 2500.00,
            ]
        ]);

        $response = $this->post(route('sales.quotations.store'), [
            'customer_id' => $this->customer->id,
            'cart_data' => $cartData,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString('OUT OF STOCK', session('error'));
        $this->assertEquals(0, Quotation::count());
    }

    /** @test */
    public function test_requesting_quantity_greater_than_available_stock_is_rejected(): void
    {
        $product = Product::create([
            'code' => 'PRD-00012',
            'sku' => 'SKU-SHORTAGE-TEST',
            'name' => 'Ergonomic Standing Desk',
            'physical_stock' => 5,
            'reserved_stock' => 2,
            'available_stock' => 3,
            'selling_price' => 35000.00,
            'status' => 'active',
        ]);

        $cartData = json_encode([
            [
                'product_id' => $product->id,
                'quantity' => 5, // 5 > available 3
                'unit_price' => 35000.00,
            ]
        ]);

        $response = $this->post(route('sales.quotations.store'), [
            'customer_id' => $this->customer->id,
            'cart_data' => $cartData,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString('Insufficient Stock', session('error'));
        $this->assertStringContainsString('Requested: 5, Available: 3, Shortage: 2', session('error'));
        $this->assertEquals(0, Quotation::count());
    }

    /** @test */
    public function test_quotation_can_be_edited_and_item_quantities_validated(): void
    {
        $product1 = Product::create([
            'code' => 'PRD-00013',
            'sku' => 'SKU-ITEM-1',
            'name' => 'USB-C Docking Station',
            'physical_stock' => 20,
            'reserved_stock' => 0,
            'available_stock' => 20,
            'selling_price' => 12000.00,
            'status' => 'active',
        ]);

        $quotation = Quotation::create([
            'quotation_number' => 'QTN-2026-00088',
            'customer_id' => $this->customer->id,
            'salesperson_id' => $this->user->id,
            'quotation_date' => date('Y-m-d'),
            'validity_date' => date('Y-m-d', strtotime('+30 days')),
            'status' => 'draft',
            'subtotal' => 12000.00,
            'taxable_amount' => 12000.00,
            'grand_total' => 14160.00,
        ]);

        QuotationItem::create([
            'quotation_id' => $quotation->id,
            'product_id' => $product1->id,
            'quantity' => 1,
            'unit_price' => 12000.00,
            'taxable_value' => 12000.00,
            'line_total' => 14160.00,
        ]);

        // Edit quotation to increase quantity to 3
        $updatedCartData = json_encode([
            [
                'product_id' => $product1->id,
                'quantity' => 3,
                'unit_price' => 12000.00,
            ]
        ]);

        $response = $this->put(route('sales.quotations.update', $quotation->id), [
            'customer_id' => $this->customer->id,
            'cart_data' => $updatedCartData,
        ]);

        $response->assertRedirect(route('sales.quotations.show', $quotation->id));
        $response->assertSessionHas('success');

        $quotation->refresh();
        $this->assertEquals(1, $quotation->items->count());
        $this->assertEquals(3, $quotation->items->first()->quantity);
    }

    /** @test */
    public function test_quotation_deletion_works_for_draft_quotations(): void
    {
        $quotation = Quotation::create([
            'quotation_number' => 'QTN-2026-00099',
            'customer_id' => $this->customer->id,
            'salesperson_id' => $this->user->id,
            'quotation_date' => date('Y-m-d'),
            'validity_date' => date('Y-m-d', strtotime('+30 days')),
            'status' => 'draft',
            'subtotal' => 5000.00,
            'grand_total' => 5900.00,
        ]);

        $response = $this->delete(route('sales.quotations.destroy', $quotation->id));

        $response->assertRedirect(route('sales.quotations.index'));
        $response->assertSessionHas('success');
        $this->assertNull(Quotation::find($quotation->id));
    }

    /** @test */
    public function test_converted_quotation_cannot_be_edited_or_deleted(): void
    {
        $order = SalesOrder::create([
            'order_number' => 'SO-2026-00100',
            'customer_id' => $this->customer->id,
            'salesperson_id' => $this->user->id,
            'warehouse_id' => $this->warehouse->id,
            'order_date' => date('Y-m-d'),
            'status' => 'approved',
            'subtotal' => 5000,
            'grand_total' => 5900,
        ]);

        $quotation = Quotation::create([
            'quotation_number' => 'QTN-2026-00100',
            'customer_id' => $this->customer->id,
            'salesperson_id' => $this->user->id,
            'validity_date' => date('Y-m-d', strtotime('+30 days')),
            'status' => 'converted',
            'sales_order_id' => $order->id,
            'subtotal' => 5000,
            'grand_total' => 5900,
        ]);

        // Attempt edit
        $editResponse = $this->get(route('sales.quotations.edit', $quotation->id));
        $editResponse->assertRedirect(route('sales.quotations.show', $quotation->id));
        $editResponse->assertSessionHas('error');

        // Attempt delete
        $deleteResponse = $this->delete(route('sales.quotations.destroy', $quotation->id));
        $deleteResponse->assertRedirect(route('sales.quotations.show', $quotation->id));
        $deleteResponse->assertSessionHas('error');

        // Ensure quotation still exists
        $this->assertNotNull(Quotation::find($quotation->id));
    }
}

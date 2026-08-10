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

class QuotationConversionStockValidationTest extends TestCase
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
            'company_name' => 'Acme Corporation',
            'contact_person' => 'John Doe',
            'email' => 'john@acme.com',
            'phone' => '9876543210',
            'status' => 'active',
        ]);

        $this->warehouse = Warehouse::create([
            'name' => 'Central Logistics Hub',
            'code' => 'WH-MAIN',
            'city' => 'Mumbai',
            'status' => 'active',
        ]);
    }

    /** @test */
    public function test_insufficient_stock_prevents_sales_order_creation_gracefully(): void
    {
        $product = Product::create([
            'code' => 'PRD-00001',
            'sku' => 'SKU-LOGI-MX3S',
            'name' => 'MX Master 3S Wireless Performance Mouse',
            'physical_stock' => 0,
            'reserved_stock' => 0,
            'available_stock' => 0,
            'unit_price' => 8999.00,
            'status' => 'active',
        ]);

        $quotation = Quotation::create([
            'quotation_number' => 'QTN-2026-00004',
            'customer_id' => $this->customer->id,
            'salesperson_id' => $this->user->id,
            'quotation_date' => date('Y-m-d'),
            'validity_date' => date('Y-m-d', strtotime('+30 days')),
            'status' => 'approved',
            'subtotal' => 8999.00,
            'taxable_amount' => 8999.00,
            'grand_total' => 8999.00,
        ]);

        QuotationItem::create([
            'quotation_id' => $quotation->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 8999.00,
            'taxable_value' => 8999.00,
            'line_total' => 8999.00,
        ]);

        $response = $this->post(route('sales.quotations.convert', $quotation->id));

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $errorMsg = session('error');
        $this->assertStringContainsString('Insufficient available stock', $errorMsg);
        $this->assertStringContainsString('MX Master 3S Wireless Performance Mouse', $errorMsg);
        $this->assertStringContainsString('Available=0', $errorMsg);

        // Verify state integrity
        $this->assertEquals(0, SalesOrder::count());
        $quotation->refresh();
        $this->assertEquals('approved', $quotation->status);
        $this->assertNull($quotation->sales_order_id);
    }

    /** @test */
    public function test_sufficient_stock_allows_successful_sales_order_conversion(): void
    {
        $product = Product::create([
            'code' => 'PRD-00002',
            'sku' => 'SKU-DELL-U2723QE',
            'name' => 'Dell UltraSharp 27 4K USB-C Hub Monitor',
            'physical_stock' => 15,
            'reserved_stock' => 0,
            'available_stock' => 15,
            'unit_price' => 45000.00,
            'status' => 'active',
        ]);

        $quotation = Quotation::create([
            'quotation_number' => 'QTN-2026-00005',
            'customer_id' => $this->customer->id,
            'salesperson_id' => $this->user->id,
            'quotation_date' => date('Y-m-d'),
            'validity_date' => date('Y-m-d', strtotime('+30 days')),
            'status' => 'approved',
            'subtotal' => 45000.00,
            'taxable_amount' => 45000.00,
            'grand_total' => 45000.00,
        ]);

        QuotationItem::create([
            'quotation_id' => $quotation->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 45000.00,
            'taxable_value' => 90000.00,
            'line_total' => 90000.00,
        ]);

        $response = $this->post(route('sales.quotations.convert', $quotation->id));

        $order = SalesOrder::first();
        $this->assertNotNull($order);

        $response->assertRedirect(route('sales.orders.show', $order->id));
        $response->assertSessionHas('success');

        $quotation->refresh();
        $this->assertEquals('converted', $quotation->status);
        $this->assertEquals($order->id, $quotation->sales_order_id);
    }

    /** @test */
    public function test_multiple_item_shortages_are_all_reported_in_error_message(): void
    {
        $p1 = Product::create([
            'code' => 'PRD-00003',
            'sku' => 'SKU-A',
            'name' => 'Keyboard Mechanical K8',
            'physical_stock' => 1,
            'reserved_stock' => 0,
            'available_stock' => 1,
            'unit_price' => 3000.00,
            'status' => 'active',
        ]);

        $p2 = Product::create([
            'code' => 'PRD-00004',
            'sku' => 'SKU-B',
            'name' => 'Ergonomic Desk Chair Pro',
            'physical_stock' => 0,
            'reserved_stock' => 0,
            'available_stock' => 0,
            'unit_price' => 15000.00,
            'status' => 'active',
        ]);

        $quotation = Quotation::create([
            'quotation_number' => 'QTN-2026-00006',
            'customer_id' => $this->customer->id,
            'salesperson_id' => $this->user->id,
            'quotation_date' => date('Y-m-d'),
            'validity_date' => date('Y-m-d', strtotime('+30 days')),
            'status' => 'approved',
            'subtotal' => 36000.00,
            'taxable_amount' => 36000.00,
            'grand_total' => 36000.00,
        ]);

        QuotationItem::create([
            'quotation_id' => $quotation->id,
            'product_id' => $p1->id,
            'quantity' => 5,
            'unit_price' => 3000.00,
            'taxable_value' => 15000.00,
            'line_total' => 15000.00,
        ]);

        QuotationItem::create([
            'quotation_id' => $quotation->id,
            'product_id' => $p2->id,
            'quantity' => 2,
            'unit_price' => 10500.00,
            'taxable_value' => 21000.00,
            'line_total' => 21000.00,
        ]);

        $response = $this->post(route('sales.quotations.convert', $quotation->id));

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $errorMsg = session('error');
        $this->assertStringContainsString('Keyboard Mechanical K8', $errorMsg);
        $this->assertStringContainsString('Ergonomic Desk Chair Pro', $errorMsg);
        $this->assertEquals(0, SalesOrder::count());
    }

    /** @test */
    public function test_duplicate_conversion_attempt_is_rejected(): void
    {
        $order = SalesOrder::create([
            'order_number' => 'SO-2026-00099',
            'customer_id' => $this->customer->id,
            'salesperson_id' => $this->user->id,
            'warehouse_id' => $this->warehouse->id,
            'order_date' => date('Y-m-d'),
            'status' => 'approved',
            'subtotal' => 1000,
            'grand_total' => 1000,
        ]);

        $quotation = Quotation::create([
            'quotation_number' => 'QTN-2026-00007',
            'customer_id' => $this->customer->id,
            'salesperson_id' => $this->user->id,
            'validity_date' => date('Y-m-d', strtotime('+30 days')),
            'status' => 'converted',
            'sales_order_id' => $order->id,
            'subtotal' => 1000,
            'grand_total' => 1000,
        ]);

        $response = $this->post(route('sales.quotations.convert', $quotation->id));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString('already been converted', session('error'));
    }
}

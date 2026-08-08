<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Tax;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductDataIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_can_register_product_with_valid_foreign_keys(): void
    {
        $category = Category::create(['name' => 'Industrial Tools', 'slug' => 'industrial-tools']);
        $brand = Brand::create(['name' => 'Bosch Power', 'slug' => 'bosch-power']);
        $unit = Unit::create(['name' => 'Piece', 'short_name' => 'pcs']);
        $tax = Tax::create(['name' => 'GST 18%', 'rate' => 18.00]);

        $payload = [
            'name' => 'Cordless Impact Drill 18V',
            'product_type' => 'single',
            'status' => 'active',
            'purchase_price' => 2500.00,
            'cost_price' => 2500.00,
            'selling_price' => 3499.00,
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'unit_id' => $unit->id,
            'tax_id' => $tax->id,
        ];

        $response = $this->actingAs($this->user)->post(route('products.store'), $payload);
        $response->assertStatus(302);

        $this->assertDatabaseHas('products', [
            'name' => 'Cordless Impact Drill 18V',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'unit_id' => $unit->id,
            'tax_id' => $tax->id,
        ]);
    }

    /** @test */
    public function it_can_register_product_with_omitted_nullable_foreign_keys(): void
    {
        $payload = [
            'name' => 'Generic Steel Washer M8',
            'product_type' => 'single',
            'status' => 'active',
            'purchase_price' => 5.00,
            'cost_price' => 5.00,
            'selling_price' => 10.00,
        ];

        $response = $this->actingAs($this->user)->post(route('products.store'), $payload);
        $response->assertStatus(302);

        $this->assertDatabaseHas('products', [
            'name' => 'Generic Steel Washer M8',
            'category_id' => null,
            'brand_id' => null,
            'unit_id' => null,
            'tax_id' => null,
        ]);
    }

    /** @test */
    public function it_sanitizes_zero_and_empty_string_foreign_keys_to_null(): void
    {
        $payload = [
            'name' => 'Adjustable Spanner 10-inch',
            'product_type' => 'single',
            'status' => 'active',
            'purchase_price' => 150.00,
            'cost_price' => 150.00,
            'selling_price' => 249.00,
            'category_id' => '0',
            'brand_id' => 0,
            'unit_id' => '',
            'tax_id' => 'null',
        ];

        $response = $this->actingAs($this->user)->post(route('products.store'), $payload);
        $response->assertStatus(302);

        $this->assertDatabaseHas('products', [
            'name' => 'Adjustable Spanner 10-inch',
            'category_id' => null,
            'brand_id' => null,
            'unit_id' => null,
            'tax_id' => null,
        ]);
    }

    /** @test */
    public function it_rejects_non_existent_foreign_keys_via_validation(): void
    {
        $payload = [
            'name' => 'Invalid Foreign Key Product',
            'product_type' => 'single',
            'status' => 'active',
            'purchase_price' => 100.00,
            'cost_price' => 100.00,
            'selling_price' => 150.00,
            'category_id' => 999999,
        ];

        $response = $this->actingAs($this->user)->post(route('products.store'), $payload);
        $response->assertSessionHasErrors(['category_id']);
        $this->assertDatabaseMissing('products', ['name' => 'Invalid Foreign Key Product']);
    }
}

<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Super Admin User
        /** @var User $user */
        $user = User::firstOrCreate(
            ['email' => 'admin@stockmanager.com'],
            [
                'name' => 'Enterprise Administrator',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $role = Role::where('slug', 'super-admin')->orWhere('slug', 'super_admin')->first();
        if ($role) {
            $user->assignRole($role);
        }

        // 2. Create Sample Categories
        $electronics = Category::firstOrCreate(['code' => 'CAT-ELEC'], [
            'name' => 'Electronics & Hardware',
            'code' => 'CAT-ELEC',
            'status' => 'active',
        ]);

        $office = Category::firstOrCreate(['code' => 'CAT-OFFICE'], [
            'name' => 'Office Supplies',
            'code' => 'CAT-OFFICE',
            'status' => 'active',
        ]);

        // 3. Create Sample Brands
        $logitech = Brand::firstOrCreate(['code' => 'BRD-LOGI'], [
            'name' => 'Logitech International',
            'code' => 'BRD-LOGI',
            'status' => 'active',
        ]);

        $dell = Brand::firstOrCreate(['code' => 'BRD-DELL'], [
            'name' => 'Dell Technologies',
            'code' => 'BRD-DELL',
            'status' => 'active',
        ]);

        // 4. Create Sample Product Attribute
        ProductAttribute::firstOrCreate(['code' => 'COLOR'], [
            'name' => 'Color Specification',
            'type' => 'text',
            'status' => 'active',
            'display_order' => 1,
        ]);

        // 5. Create Sample Products
        Product::firstOrCreate(['sku' => 'SKU-LOGI-MX3S'], [
            'name' => 'MX Master 3S Wireless Performance Mouse',
            'code' => 'PRD-MX3S',
            'barcode' => '8901234567890',
            'category_id' => $electronics->id,
            'brand_id' => $logitech->id,
            'product_type' => 'single',
            'status' => 'active',
            'purchase_price' => 6500.00,
            'cost_price' => 6500.00,
            'selling_price' => 8999.00,
            'mrp' => 9999.00,
            'physical_stock' => 150,
            'reserved_stock' => 10,
            'available_stock' => 140,
            'min_stock' => 20,
            'reorder_level' => 30,
            'warehouse_location' => 'WH-MAIN-A1',
            'rack_location' => 'RACK-04-B',
            'description' => '8K DPI any-surface tracking, quiet clicks, ergonomic design.',
            'created_by' => $user->id,
        ]);

        Product::firstOrCreate(['sku' => 'SKU-DELL-U2723QE'], [
            'name' => 'Dell UltraSharp 27 4K USB-C Hub Monitor',
            'code' => 'PRD-DELL27',
            'barcode' => '8909876543210',
            'category_id' => $electronics->id,
            'brand_id' => $dell->id,
            'product_type' => 'single',
            'status' => 'active',
            'purchase_price' => 38000.00,
            'cost_price' => 38000.00,
            'selling_price' => 49999.00,
            'mrp' => 54999.00,
            'physical_stock' => 25,
            'reserved_stock' => 2,
            'available_stock' => 23,
            'min_stock' => 5,
            'reorder_level' => 10,
            'warehouse_location' => 'WH-MAIN-C3',
            'rack_location' => 'RACK-12-A',
            'description' => '27-inch 4K UHD monitor with IPS Black technology.',
            'created_by' => $user->id,
        ]);
    }
}

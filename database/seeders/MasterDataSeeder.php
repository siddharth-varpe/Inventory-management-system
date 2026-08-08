<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Tax;
use App\Models\Unit;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Categories
        $categories = [
            ['name' => 'Industrial Electronics', 'code' => 'CAT-ELEC-01', 'description' => 'Sensors, controllers, and wiring components'],
            ['name' => 'Hardware & Tools', 'code' => 'CAT-HARD-02', 'description' => 'Fasteners, hand tools, and power tools'],
            ['name' => 'Raw Materials', 'code' => 'CAT-RAW-03', 'description' => 'Metals, polymers, and chemical compounds'],
            ['name' => 'Packaging Materials', 'code' => 'CAT-PACK-04', 'description' => 'Boxes, straps, and protective bubble wrap'],
            ['name' => 'Safety & Compliance', 'code' => 'CAT-SAFE-05', 'description' => 'Helmets, gloves, and protective gear'],
        ];

        foreach ($categories as $catData) {
            Category::updateOrCreate(['code' => $catData['code']], $catData);
        }

        // 2. Brands
        $brands = [
            ['name' => 'Bosch Industrial', 'code' => 'BRD-BOSCH', 'manufacturer' => 'Bosch GmbH'],
            ['name' => 'Siemens Automation', 'code' => 'BRD-SIEMENS', 'manufacturer' => 'Siemens AG'],
            ['name' => 'Schneider Electric', 'code' => 'BRD-SCHNEIDER', 'manufacturer' => 'Schneider SE'],
            ['name' => '3M Safety', 'code' => 'BRD-3MSAFE', 'manufacturer' => '3M Company'],
        ];

        foreach ($brands as $bData) {
            Brand::updateOrCreate(['code' => $bData['code']], $bData);
        }

        // Get created master references
        $elecCategory = Category::where('code', 'CAT-ELEC-01')->first();
        $hardCategory = Category::where('code', 'CAT-HARD-02')->first();
        $boschBrand = Brand::where('code', 'BRD-BOSCH')->first();
        $siemensBrand = Brand::where('code', 'BRD-SIEMENS')->first();
        $pcsUnit = Unit::first();
        $gstTax = Tax::first();

        // 3. Products
        $products = [
            [
                'name' => 'Digital Pressure Sensor 24V',
                'code' => 'PRD-SENS-001',
                'sku' => 'SKU-SENS-001',
                'barcode' => '8901001001001',
                'category_id' => $elecCategory?->id,
                'brand_id' => $siemensBrand?->id,
                'unit_id' => $pcsUnit?->id,
                'tax_id' => $gstTax?->id,
                'product_type' => 'single',
                'status' => 'active',
                'purchase_price' => 1200.00,
                'cost_price' => 1200.00,
                'selling_price' => 1899.00,
                'physical_stock' => 50,
                'min_stock' => 10,
                'reorder_level' => 15,
                'warehouse_location' => 'Main Warehouse',
                'rack_location' => 'Rack A1-04',
            ],
            [
                'name' => 'Heavy Duty Angle Grinder 850W',
                'code' => 'PRD-GRND-002',
                'sku' => 'SKU-GRND-002',
                'barcode' => '8901001001002',
                'category_id' => $hardCategory?->id,
                'brand_id' => $boschBrand?->id,
                'unit_id' => $pcsUnit?->id,
                'tax_id' => $gstTax?->id,
                'product_type' => 'single',
                'status' => 'active',
                'purchase_price' => 3200.00,
                'cost_price' => 3200.00,
                'selling_price' => 4500.00,
                'physical_stock' => 30,
                'min_stock' => 5,
                'reorder_level' => 8,
                'warehouse_location' => 'Main Warehouse',
                'rack_location' => 'Rack B2-01',
            ],
            [
                'name' => 'Industrial Safety Helmet - Yellow',
                'code' => 'PRD-HELM-003',
                'sku' => 'SKU-HELM-003',
                'barcode' => '8901001001003',
                'category_id' => $hardCategory?->id,
                'brand_id' => $boschBrand?->id,
                'unit_id' => $pcsUnit?->id,
                'tax_id' => $gstTax?->id,
                'product_type' => 'single',
                'status' => 'active',
                'purchase_price' => 250.00,
                'cost_price' => 250.00,
                'selling_price' => 499.00,
                'physical_stock' => 120,
                'min_stock' => 20,
                'reorder_level' => 30,
                'warehouse_location' => 'Main Warehouse',
                'rack_location' => 'Rack S-01',
            ],
        ];

        foreach ($products as $p) {
            Product::updateOrCreate(['code' => $p['code']], $p);
        }
    }
}

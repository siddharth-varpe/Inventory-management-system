<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $warehouses = [
            [
                'code' => 'WH-MAIN-001',
                'name' => 'Central Master Warehouse',
                'type' => 'distribution_center',
                'address' => 'Logistics Park, Hub 1, Mumbai, MH',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'postal_code' => '400001',
                'contact_phone' => '+91 98200 99887',
                'status' => 'active',
            ],
            [
                'code' => 'WH-BLR-002',
                'name' => 'Southern Tech Distribution Center',
                'type' => 'storage',
                'address' => 'Electronic City Hub, Bengaluru, KA',
                'city' => 'Bengaluru',
                'state' => 'Karnataka',
                'postal_code' => '560100',
                'contact_phone' => '+91 98450 11223',
                'status' => 'active',
            ],
        ];

        foreach ($warehouses as $w) {
            Warehouse::updateOrCreate(['code' => $w['code']], $w);
        }
    }
}

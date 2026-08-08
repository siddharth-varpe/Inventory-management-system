<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run unit seeds.
     */
    public function run(): void
    {
        $units = [
            ['name' => 'Piece', 'short_name' => 'pc', 'symbol' => 'PC', 'decimal_precision' => 0, 'is_default' => true],
            ['name' => 'Box', 'short_name' => 'box', 'symbol' => 'BOX', 'decimal_precision' => 0, 'is_default' => false],
            ['name' => 'Packet', 'short_name' => 'pkt', 'symbol' => 'PKT', 'decimal_precision' => 0, 'is_default' => false],
            ['name' => 'Kilogram', 'short_name' => 'kg', 'symbol' => 'KG', 'decimal_precision' => 3, 'is_default' => false],
            ['name' => 'Gram', 'short_name' => 'g', 'symbol' => 'G', 'decimal_precision' => 2, 'is_default' => false],
            ['name' => 'Litre', 'short_name' => 'l', 'symbol' => 'L', 'decimal_precision' => 2, 'is_default' => false],
            ['name' => 'Meter', 'short_name' => 'm', 'symbol' => 'M', 'decimal_precision' => 2, 'is_default' => false],
            ['name' => 'Feet', 'short_name' => 'ft', 'symbol' => 'FT', 'decimal_precision' => 2, 'is_default' => false],
            ['name' => 'Roll', 'short_name' => 'roll', 'symbol' => 'RL', 'decimal_precision' => 0, 'is_default' => false],
            ['name' => 'Carton', 'short_name' => 'ctn', 'symbol' => 'CTN', 'decimal_precision' => 0, 'is_default' => false],
            ['name' => 'Dozen', 'short_name' => 'doz', 'symbol' => 'DOZ', 'decimal_precision' => 0, 'is_default' => false],
        ];

        foreach ($units as $unit) {
            Unit::firstOrCreate(['short_name' => $unit['short_name']], $unit);
        }
    }
}

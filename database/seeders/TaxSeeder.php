<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Tax;
use Illuminate\Database\Seeder;

class TaxSeeder extends Seeder
{
    /**
     * Run tax seeds.
     */
    public function run(): void
    {
        $taxes = [
            ['name' => 'GST 18%', 'code' => 'GST-18', 'rate' => 18.00, 'type' => 'gst', 'description' => 'Standard 18% Goods & Services Tax'],
            ['name' => 'GST 12%', 'code' => 'GST-12', 'rate' => 12.00, 'type' => 'gst', 'description' => 'Standard 12% Goods & Services Tax'],
            ['name' => 'GST 5%', 'code' => 'GST-5', 'rate' => 5.00, 'type' => 'gst', 'description' => 'Reduced 5% Goods & Services Tax'],
            ['name' => 'CGST 9%', 'code' => 'CGST-9', 'rate' => 9.00, 'type' => 'cgst', 'description' => 'Central GST 9%'],
            ['name' => 'SGST 9%', 'code' => 'SGST-9', 'rate' => 9.00, 'type' => 'sgst', 'description' => 'State GST 9%'],
            ['name' => 'IGST 18%', 'code' => 'IGST-18', 'rate' => 18.00, 'type' => 'igst', 'description' => 'Integrated GST 18%'],
            ['name' => 'CESS 12%', 'code' => 'CESS-12', 'rate' => 12.00, 'type' => 'cess', 'description' => 'Compensation CESS 12%'],
        ];

        foreach ($taxes as $tax) {
            Tax::firstOrCreate(['code' => $tax['code']], $tax);
        }
    }
}

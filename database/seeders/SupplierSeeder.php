<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            [
                'code' => 'SUP-APEX-001',
                'name' => 'Apex Industrial Global',
                'tax_number' => 'GSTIN27AAACA1234F1Z5',
                'email' => 'orders@apexindustrial.com',
                'phone' => '+91 98200 11223',
                'address' => 'Plot 45, MIDC Industrial Zone, Mumbai, MH',
                'contact_person' => 'Rajesh Sharma',
                'payment_terms' => 'Net 30',
                'rating' => 4.85,
                'status' => 'active',
                'created_by' => 1,
            ],
            [
                'code' => 'SUP-LOGIX-002',
                'name' => 'LogixTech Components Ltd',
                'tax_number' => 'GSTIN29BBBCA9876G1Z2',
                'email' => 'supply@logixtech.com',
                'phone' => '+91 98450 33445',
                'address' => 'Electronics City Phase I, Bengaluru, KA',
                'contact_person' => 'Ananya Rao',
                'payment_terms' => 'Net 15',
                'rating' => 4.60,
                'status' => 'active',
                'created_by' => 1,
            ],
            [
                'code' => 'SUP-ZENITH-003',
                'name' => 'Zenith Packaging Solutions',
                'tax_number' => 'GSTIN07CCCCA5555H1Z9',
                'email' => 'sales@zenithpack.com',
                'phone' => '+91 98110 55667',
                'address' => 'Okhla Industrial Area Phase III, New Delhi, DL',
                'contact_person' => 'Vikram Malhotra',
                'payment_terms' => 'Net 45',
                'rating' => 4.40,
                'status' => 'active',
                'created_by' => 1,
            ],
        ];

        foreach ($suppliers as $s) {
            Supplier::updateOrCreate(['code' => $s['code']], $s);
        }
    }
}

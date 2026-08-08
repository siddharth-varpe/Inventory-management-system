<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run department seeds.
     */
    public function run(): void
    {
        $departments = [
            ['name' => 'Administration', 'code' => 'DEPT-ADM', 'description' => 'Executive administration & corporate management.'],
            ['name' => 'Inventory', 'code' => 'DEPT-INV', 'description' => 'Stock tracking & inventory control.'],
            ['name' => 'Warehouse', 'code' => 'DEPT-WH', 'description' => 'Warehouse operations & storage.'],
            ['name' => 'Sales', 'code' => 'DEPT-SALES', 'description' => 'Sales & customer management.'],
            ['name' => 'Purchasing', 'code' => 'DEPT-PURCH', 'description' => 'Procurement & vendor purchasing.'],
            ['name' => 'Accounts', 'code' => 'DEPT-ACC', 'description' => 'Accounts & invoicing.'],
            ['name' => 'Finance', 'code' => 'DEPT-FIN', 'description' => 'Corporate finance & budgeting.'],
            ['name' => 'HR', 'code' => 'DEPT-HR', 'description' => 'Human resources & personnel.'],
            ['name' => 'Production', 'code' => 'DEPT-PROD', 'description' => 'Production & assembly line operations.'],
            ['name' => 'Logistics', 'code' => 'DEPT-LOG', 'description' => 'Shipping & logistics management.'],
            ['name' => 'Quality Control', 'code' => 'DEPT-QC', 'description' => 'Quality control & testing.'],
        ];

        foreach ($departments as $dept) {
            Department::firstOrCreate(['code' => $dept['code']], $dept);
        }
    }
}

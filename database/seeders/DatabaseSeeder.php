<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            DepartmentSeeder::class,
            CompanySeeder::class,
            UnitSeeder::class,
            TaxSeeder::class,
            UserSeeder::class,
            PortalModuleSeeder::class,
            SupplierSeeder::class,
            WarehouseSeeder::class,
            MasterDataSeeder::class,
        ]);
    }
}

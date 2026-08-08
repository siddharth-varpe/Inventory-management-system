<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run company and default branch seeds.
     */
    public function run(): void
    {
        $company = Company::firstOrCreate(
            ['name' => 'StockManager Enterprise ERP'],
            [
                'legal_name' => 'StockManager Enterprise India Pvt Ltd',
                'business_type' => 'Private Limited Company',
                'industry' => 'Enterprise Software Solutions',
                'email' => 'corporate@stockmanager-erp.in',
                'currency' => 'INR',
                'timezone' => 'Asia/Kolkata',
                'financial_year' => 'Apr-Mar',
                'language' => 'en_IN',
                'status' => 'active',
            ]
        );

        Branch::firstOrCreate(
            ['code' => 'BR-HQ-01'],
            [
                'company_id' => $company->id,
                'name' => 'Headquarters Main Branch (Mumbai)',
                'phone' => '+91 98765 43210',
                'email' => 'hq@stockmanager-erp.in',
                'country' => 'India',
                'status' => 'active',
            ]
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\Sales;

use App\Models\Company;
use App\Models\Customer;

class SalesGstCalculator
{
    /**
     * Calculate Tax split (CGST, SGST, IGST) for a given taxable amount and GST rate.
     */
    public function calculateTax(float $taxableAmount, float $gstRate, ?Customer $customer): array
    {
        $totalTaxAmount = round(($taxableAmount * $gstRate) / 100.0, 2);

        $company = Company::first();
        $companyState = strtolower(trim($company->state ?? 'Maharashtra'));

        // Retrieve primary billing address state
        $customerState = strtolower(trim($customer?->addresses()?->where('type', 'billing')->first()?->state ?? $companyState));

        $isInterState = ($customerState !== $companyState);

        if ($isInterState) {
            return [
                'cgst' => 0.00,
                'sgst' => 0.00,
                'igst' => $totalTaxAmount,
                'total_tax' => $totalTaxAmount,
                'is_interstate' => true,
            ];
        }

        // Intra-State: 50% CGST + 50% SGST
        $halfTax = round($totalTaxAmount / 2.0, 2);
        return [
            'cgst' => $halfTax,
            'sgst' => $halfTax,
            'igst' => 0.00,
            'total_tax' => $totalTaxAmount,
            'is_interstate' => false,
        ];
    }
}

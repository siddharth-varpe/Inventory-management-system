<?php

declare(strict_types=1);

namespace App\Domain\Sales;

use App\Models\Customer;
use App\Models\Product;

class CustomerPricingService
{
    /**
     * Determine custom selling price for a product based on Customer Type & Tier.
     */
    public function getCustomerPrice(Product $product, ?Customer $customer): float
    {
        $baseSellingPrice = (float)$product->selling_price;
        if ($baseSellingPrice <= 0) {
            $baseSellingPrice = (float)$product->cost_price * 1.30; // Default 30% margin fallback
        }

        if (!$customer) {
            return $baseSellingPrice;
        }

        // Apply Tier Multipliers based on Customer Type
        $multiplier = match ($customer->customer_type) {
            'distributor' => 0.75, // 25% discount off base retail
            'dealer'      => 0.85, // 15% discount
            'corporate'   => 0.80, // 20% discount
            'oem'         => 0.70, // 30% discount
            'government'  => 0.90, // 10% discount
            'institution' => 0.88, // 12% discount
            default       => 1.00, // Retail price
        };

        return round($baseSellingPrice * $multiplier, 2);
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\Communication;

use App\Models\Customer;
use App\Domain\Communication\ValueObjects\CommunicationProfile;

class CustomerProfileResolver
{
    /**
     * Resolve Customer Master into a CommunicationProfile Value Object.
     */
    public function resolve(Customer $customer): CommunicationProfile
    {
        $customer->loadMissing('addresses');

        $billing = $customer->addresses->where('address_type', 'billing')->first();
        $shipping = $customer->addresses->where('address_type', 'shipping')->first() ?? $billing;

        $formatAddr = function ($addr) {
            if (!$addr) return null;
            $parts = array_filter([
                $addr->address_line1 ?? null,
                $addr->address_line2 ?? null,
                $addr->city ?? null,
                $addr->state ?? null,
                $addr->pincode ?? null,
                $addr->country ?? 'India',
            ]);
            return implode(', ', $parts);
        };

        return new CommunicationProfile(
            customerId: (int)$customer->id,
            customerCode: (string)($customer->customer_code ?? 'CUST-' . $customer->id),
            companyName: (string)($customer->company_name ?? 'N/A'),
            contactPerson: $customer->contact_person,
            email: $customer->email,
            mobile: $customer->phone,
            whatsapp: $customer->whatsapp ?? $customer->phone,
            preferredChannel: strtolower($customer->preferred_communication_channel ?? 'email'),
            preferredLanguage: $customer->preferred_language ?? 'en',
            isActive: (string)$customer->status === 'active',
            billingAddress: $formatAddr($billing),
            shippingAddress: $formatAddr($shipping),
            notificationPreferences: [
                'email_enabled' => true,
                'whatsapp_enabled' => true,
                'sms_enabled' => false,
            ]
        );
    }
}

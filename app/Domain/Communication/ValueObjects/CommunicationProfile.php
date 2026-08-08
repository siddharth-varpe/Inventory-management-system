<?php

declare(strict_types=1);

namespace App\Domain\Communication\ValueObjects;

class CommunicationProfile
{
    public function __construct(
        public readonly int $customerId,
        public readonly string $customerCode,
        public readonly string $companyName,
        public readonly ?string $contactPerson,
        public readonly ?string $email,
        public readonly ?string $mobile,
        public readonly ?string $whatsapp,
        public readonly string $preferredChannel,
        public readonly string $preferredLanguage,
        public readonly bool $isActive,
        public readonly ?string $billingAddress,
        public readonly ?string $shippingAddress,
        public readonly array $notificationPreferences = []
    ) {}

    public function toArray(): array
    {
        return [
            'customer_id' => $this->customerId,
            'customer_code' => $this->customerCode,
            'company_name' => $this->companyName,
            'contact_person' => $this->contactPerson,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'whatsapp' => $this->whatsapp,
            'preferred_channel' => $this->preferredChannel,
            'preferred_language' => $this->preferredLanguage,
            'is_active' => $this->isActive,
            'billing_address' => $this->billingAddress,
            'shipping_address' => $this->shippingAddress,
            'notification_preferences' => $this->notificationPreferences,
        ];
    }
}

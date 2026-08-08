<?php

declare(strict_types=1);

namespace App\Domain\Communication;

use App\Models\Customer;
use App\Domain\Communication\ValueObjects\CommunicationProfile;

class CommunicationValidationEngine
{
    /**
     * Authorized departments for commercial communications.
     */
    protected const AUTHORIZED_DEPARTMENTS = [
        'sales', 'crm', 'accounts', 'administration', 'billing', 'procurement', 'management'
    ];

    /**
     * Validate communication request data & customer profile.
     */
    public function validate(Customer $customer, CommunicationProfile $profile, string $documentType, int $documentId, string $department): array
    {
        $errors = [];

        if (!$profile->isActive) {
            $errors[] = "Customer account '{$customer->company_name}' (ID: {$customer->id}) is currently INACTIVE.";
        }

        if (empty($profile->email) && empty($profile->mobile)) {
            $errors[] = "Customer '{$customer->company_name}' has no valid email or mobile number registered.";
        }

        if (!empty($profile->email) && !filter_var($profile->email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid recipient email address format: '{$profile->email}'.";
        }

        if (!empty($profile->mobile) && strlen(preg_replace('/[^0-9]/', '', $profile->mobile)) < 8) {
            $errors[] = "Invalid recipient mobile number format: '{$profile->mobile}'.";
        }

        if (empty($documentType) || $documentId <= 0) {
            $errors[] = "Invalid related document reference specified (Type: {$documentType}, ID: {$documentId}).";
        }

        $deptLower = strtolower(trim($department));
        if (!in_array($deptLower, self::AUTHORIZED_DEPARTMENTS, true)) {
            $errors[] = "Department '{$department}' is not authorized to initiate commercial customer communications.";
        }

        return $errors;
    }
}

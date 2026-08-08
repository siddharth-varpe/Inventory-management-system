<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Company;
use Illuminate\Http\UploadedFile;

interface CompanyServiceInterface extends BaseServiceInterface
{
    /**
     * Get or create primary company profile.
     *
     * @return Company
     */
    public function getPrimaryProfile(): Company;

    /**
     * Update primary company profile.
     *
     * @param array<string, mixed> $data
     * @param UploadedFile|null $logoFile
     * @return Company
     */
    public function updateProfile(array $data, ?UploadedFile $logoFile = null): Company;
}

<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Brand;
use Illuminate\Http\UploadedFile;

interface BrandServiceInterface extends BaseServiceInterface
{
    /**
     * Create brand with optional logo.
     *
     * @param array<string, mixed> $data
     * @param UploadedFile|null $logoFile
     * @return Brand
     */
    public function createBrand(array $data, ?UploadedFile $logoFile = null): Brand;

    /**
     * Update brand with optional logo replace.
     *
     * @param int|string $id
     * @param array<string, mixed> $data
     * @param UploadedFile|null $logoFile
     * @return bool
     */
    public function updateBrand(int|string $id, array $data, ?UploadedFile $logoFile = null): bool;

    /**
     * Restore brand.
     *
     * @param int|string $id
     * @return bool
     */
    public function restoreBrand(int|string $id): bool;
}

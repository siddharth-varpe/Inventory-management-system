<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;

interface ProductServiceInterface extends BaseServiceInterface
{
    /**
     * Get paginated catalog with filters.
     *
     * @param array<string, mixed> $filters
     * @param int $perPage
     * @return LengthAwarePaginator<Product>
     */
    public function getCatalog(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Create product with SKU & Barcode generation.
     *
     * @param array<string, mixed> $data
     * @param UploadedFile|null $image
     * @param array<UploadedFile>|null $documents
     * @return Product
     */
    public function createProduct(array $data, ?UploadedFile $image = null, ?array $documents = null): Product;

    /**
     * Update product.
     *
     * @param int|string $id
     * @param array<string, mixed> $data
     * @param UploadedFile|null $image
     * @param array<UploadedFile>|null $documents
     * @return bool
     */
    public function updateProduct(int|string $id, array $data, ?UploadedFile $image = null, ?array $documents = null): bool;

    /**
     * Duplicate product.
     *
     * @param int|string $id
     * @return Product
     */
    public function duplicateProduct(int|string $id): Product;

    /**
     * Archive product.
     *
     * @param int|string $id
     * @return bool
     */
    public function archiveProduct(int|string $id): bool;

    /**
     * Restore product.
     *
     * @param int|string $id
     * @return bool
     */
    public function restoreProduct(int|string $id): bool;

    /**
     * Receive supplier stock and update weighted average cost.
     *
     * @param array<string, mixed> $data
     * @return bool
     */
    public function receiveStock(array $data): bool;

    /**
     * Adjust stock quantity.
     *
     * @param array<string, mixed> $data
     * @return bool
     */
    public function adjustStock(array $data): bool;
}

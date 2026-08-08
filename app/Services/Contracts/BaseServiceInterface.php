<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface BaseServiceInterface
{
    /**
     * Get all records.
     *
     * @param array<int, string> $columns
     * @param array<int, string> $relations
     * @return Collection<int, Model>
     */
    public function getAll(array $columns = ['*'], array $relations = []): Collection;

    /**
     * Get record by ID.
     *
     * @param int|string $id
     * @param array<int, string> $columns
     * @param array<int, string> $relations
     * @return Model
     */
    public function getById(int|string $id, array $columns = ['*'], array $relations = []): Model;

    /**
     * Create a new record.
     *
     * @param array<string, mixed> $data
     * @return Model
     */
    public function create(array $data): Model;

    /**
     * Update an existing record.
     *
     * @param int|string $id
     * @param array<string, mixed> $data
     * @return bool
     */
    public function update(int|string $id, array $data): bool;

    /**
     * Delete a record by ID.
     *
     * @param int|string $id
     * @return bool
     */
    public function delete(int|string $id): bool;

    /**
     * Get paginated records.
     *
     * @param int $perPage
     * @param array<int, string> $columns
     * @param array<int, string> $relations
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = []): LengthAwarePaginator;
}

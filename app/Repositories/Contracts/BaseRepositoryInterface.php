<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface BaseRepositoryInterface
{
    /**
     * Get all records.
     *
     * @param array<int, string> $columns
     * @param array<int, string> $relations
     * @return Collection<int, Model>
     */
    public function all(array $columns = ['*'], array $relations = []): Collection;

    /**
     * Find a record by ID.
     *
     * @param int|string $id
     * @param array<int, string> $columns
     * @param array<int, string> $relations
     * @return Model|null
     */
    public function find(int|string $id, array $columns = ['*'], array $relations = []): ?Model;

    /**
     * Find a record by ID or throw an exception.
     *
     * @param int|string $id
     * @param array<int, string> $columns
     * @param array<int, string> $relations
     * @return Model
     */
    public function findOrFail(int|string $id, array $columns = ['*'], array $relations = []): Model;

    /**
     * Find a record by field value.
     *
     * @param string $field
     * @param mixed $value
     * @param array<int, string> $columns
     * @param array<int, string> $relations
     * @return Model|null
     */
    public function findBy(string $field, mixed $value, array $columns = ['*'], array $relations = []): ?Model;

    /**
     * Create a new record.
     *
     * @param array<string, mixed> $payload
     * @return Model
     */
    public function create(array $payload): Model;

    /**
     * Update an existing record.
     *
     * @param int|string $id
     * @param array<string, mixed> $payload
     * @return bool
     */
    public function update(int|string $id, array $payload): bool;

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

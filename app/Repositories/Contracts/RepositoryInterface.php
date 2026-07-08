<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Base Repository Interface
 * 
 * Defines the contract for all repository implementations.
 * Provides standard CRUD operations and common query methods.
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
interface RepositoryInterface
{
    /**
     * Get all records.
     */
    public function all(array $columns = ['*']): Collection;

    /**
     * Get paginated records.
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator;

    /**
     * Find a record by ID.
     */
    public function find(string $id, array $columns = ['*']): ?Model;

    /**
     * Find a record by ID or throw exception.
     */
    public function findOrFail(string $id, array $columns = ['*']): Model;

    /**
     * Find records by a specific field.
     */
    public function findByField(string $field, mixed $value, array $columns = ['*']): Collection;

    /**
     * Find first record by a specific field.
     */
    public function findFirstByField(string $field, mixed $value, array $columns = ['*']): ?Model;

    /**
     * Find records matching conditions.
     */
    public function findWhere(array $conditions, array $columns = ['*']): Collection;

    /**
     * Find records where field is in array.
     */
    public function findWhereIn(string $field, array $values, array $columns = ['*']): Collection;

    /**
     * Create a new record.
     */
    public function create(array $data): Model;

    /**
     * Update a record by ID.
     */
    public function update(string $id, array $data): Model;

    /**
     * Delete a record by ID.
     */
    public function delete(string $id): bool;

    /**
     * Count all records.
     */
    public function count(): int;

    /**
     * Count records matching conditions.
     */
    public function countWhere(array $conditions): int;

    /**
     * Check if record exists by ID.
     */
    public function exists(string $id): bool;

    /**
     * Check if record exists matching conditions.
     */
    public function existsWhere(array $conditions): bool;

    /**
     * Get records with relationships.
     */
    public function with(array $relations): self;

    /**
     * Order records by a field.
     */
    public function orderBy(string $field, string $direction = 'asc'): self;

    /**
     * Get first record.
     */
    public function first(array $columns = ['*']): ?Model;

    /**
     * Get latest records.
     */
    public function latest(string $column = 'created_at'): self;
}

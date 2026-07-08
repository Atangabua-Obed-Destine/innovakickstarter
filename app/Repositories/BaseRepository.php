<?php

namespace App\Repositories;

use App\Repositories\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Base Repository Implementation
 * 
 * Abstract class providing common repository functionality.
 * All specific repositories extend this class.
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
abstract class BaseRepository implements RepositoryInterface
{
    /**
     * The model instance.
     */
    protected Model $model;

    /**
     * The query builder instance.
     */
    protected Builder $query;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->model = $this->makeModel();
        $this->resetQuery();
    }

    /**
     * Get the model class name.
     */
    abstract protected function model(): string;

    /**
     * Create a new model instance.
     */
    protected function makeModel(): Model
    {
        $model = app($this->model());

        if (!$model instanceof Model) {
            throw new \Exception("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        return $model;
    }

    /**
     * Reset the query builder.
     */
    protected function resetQuery(): void
    {
        $this->query = $this->model->newQuery();
    }

    /**
     * Get a fresh query builder and reset.
     */
    protected function newQuery(): Builder
    {
        $this->resetQuery();
        return $this->query;
    }

    /**
     * {@inheritDoc}
     */
    public function all(array $columns = ['*']): Collection
    {
        $result = $this->query->get($columns);
        $this->resetQuery();
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        $result = $this->query->paginate($perPage, $columns);
        $this->resetQuery();
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function find(string $id, array $columns = ['*']): ?Model
    {
        return $this->model->find($id, $columns);
    }

    /**
     * {@inheritDoc}
     */
    public function findOrFail(string $id, array $columns = ['*']): Model
    {
        return $this->model->findOrFail($id, $columns);
    }

    /**
     * {@inheritDoc}
     */
    public function findByField(string $field, mixed $value, array $columns = ['*']): Collection
    {
        return $this->model->where($field, $value)->get($columns);
    }

    /**
     * {@inheritDoc}
     */
    public function findFirstByField(string $field, mixed $value, array $columns = ['*']): ?Model
    {
        return $this->model->where($field, $value)->first($columns);
    }

    /**
     * {@inheritDoc}
     */
    public function findWhere(array $conditions, array $columns = ['*']): Collection
    {
        return $this->model->where($conditions)->get($columns);
    }

    /**
     * {@inheritDoc}
     */
    public function findWhereIn(string $field, array $values, array $columns = ['*']): Collection
    {
        return $this->model->whereIn($field, $values)->get($columns);
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * {@inheritDoc}
     */
    public function update(string $id, array $data): Model
    {
        $model = $this->findOrFail($id);
        $model->update($data);
        return $model->fresh();
    }

    /**
     * {@inheritDoc}
     */
    public function delete(string $id): bool
    {
        $model = $this->findOrFail($id);
        return $model->delete();
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        $result = $this->query->count();
        $this->resetQuery();
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function countWhere(array $conditions): int
    {
        return $this->model->where($conditions)->count();
    }

    /**
     * {@inheritDoc}
     */
    public function exists(string $id): bool
    {
        return $this->model->where($this->model->getKeyName(), $id)->exists();
    }

    /**
     * {@inheritDoc}
     */
    public function existsWhere(array $conditions): bool
    {
        return $this->model->where($conditions)->exists();
    }

    /**
     * {@inheritDoc}
     */
    public function with(array $relations): self
    {
        $this->query = $this->query->with($relations);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function orderBy(string $field, string $direction = 'asc'): self
    {
        $this->query = $this->query->orderBy($field, $direction);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function first(array $columns = ['*']): ?Model
    {
        $result = $this->query->first($columns);
        $this->resetQuery();
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function latest(string $column = 'created_at'): self
    {
        $this->query = $this->query->latest($column);
        return $this;
    }

    /**
     * Add a where clause to the query.
     */
    public function where(string|array $field, mixed $operator = null, mixed $value = null): self
    {
        if (is_array($field)) {
            $this->query = $this->query->where($field);
        } else {
            $this->query = $this->query->where($field, $operator, $value);
        }
        return $this;
    }

    /**
     * Add a whereIn clause to the query.
     */
    public function whereIn(string $field, array $values): self
    {
        $this->query = $this->query->whereIn($field, $values);
        return $this;
    }

    /**
     * Add a whereBetween clause to the query.
     */
    public function whereBetween(string $field, array $values): self
    {
        $this->query = $this->query->whereBetween($field, $values);
        return $this;
    }

    /**
     * Add a scope to the query.
     */
    public function scope(string $scope, ...$args): self
    {
        $this->query = $this->query->$scope(...$args);
        return $this;
    }

    /**
     * Take a specific number of records.
     */
    public function take(int $limit): self
    {
        $this->query = $this->query->take($limit);
        return $this;
    }

    /**
     * Skip a specific number of records.
     */
    public function skip(int $offset): self
    {
        $this->query = $this->query->skip($offset);
        return $this;
    }

    /**
     * Get the underlying query builder.
     */
    public function getQuery(): Builder
    {
        return $this->query;
    }

    /**
     * Get the underlying model.
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Begin a database transaction.
     */
    public function beginTransaction(): void
    {
        \DB::beginTransaction();
    }

    /**
     * Commit the database transaction.
     */
    public function commit(): void
    {
        \DB::commit();
    }

    /**
     * Rollback the database transaction.
     */
    public function rollback(): void
    {
        \DB::rollBack();
    }

    /**
     * Execute a callback within a transaction.
     */
    public function transaction(callable $callback): mixed
    {
        return \DB::transaction($callback);
    }
}

<?php

namespace App\Repositories\Base\Concretes;

use App\Repositories\Base\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use InvalidArgumentException;

abstract class BaseRepository implements BaseRepositoryInterface
{
    protected Builder|Model|Relation $model;

    /**
     * BaseRepository constructor.
     */
    public function __construct()
    {
        $this->setModel($this->model());
    }

    /**
     * Set new model. It can be: bare model, QueryBuilder, Relation,
     */
    public function setModel(Model|Builder|Relation|string $entity): void
    {
        if (is_a($entity, Model::class) || is_subclass_of($entity, Model::class)) {
            $this->model = $entity::query();
        } elseif (
            is_a($entity, Builder::class) ||
            is_subclass_of($entity, Builder::class) ||
            is_a($entity, Relation::class) ||
            is_subclass_of($entity, Relation::class)
        ) {
            $this->model = $entity;
        } elseif (is_string($entity)) {
            $this->model = app($entity)->query();
        } else {
            throw new InvalidArgumentException('Invalid entity type');
        }
    }

    /**
     * Specify Model class name
     */
    abstract protected function model(): string;

    /**
     * Get all resources
     */
    public function all(array $columns = ['*']): Collection
    {
        return $this->model->get($columns);
    }

    /**
     * Get paginated resources
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->model->paginate($perPage, $columns);
    }

    /**
     * Find resource by id
     */
    public function find(int $id, array $columns = ['*']): ?Model
    {
        return $this->model->find($id, $columns);
    }

    /**
     * Find resource by field
     */
    public function findByField(string $field, mixed $value, array $columns = ['*']): ?Model
    {
        return $this->model->where($field, $value)->first($columns);
    }

    /**
     * Find resource or fail
     */
    public function findOrFail(int $id, array $columns = ['*']): Model
    {
        return $this->model->findOrFail($id, $columns);
    }

    /**
     * Create new resource
     */
    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * Update resource
     */
    public function update(int $id, array $data): Model
    {
        $model = $this->findOrFail($id);
        $model->fill($data);
        $model->save();

        return $model->fresh();
    }

    /**
     * Delete resource
     */
    public function delete(int $id): bool
    {
        return $this->findOrFail($id)->delete();
    }

    public function deleteMany(array $ids): int
    {
        $ids = array_filter(Arr::flatten($ids)); 
        if (empty($ids)) {
            return 0;
        }
        
        $keyName = $this->model->getModel()->getKeyName();
        
        return $this->model
            ->newQuery()
            ->whereIn($keyName, $ids)
            ->delete();
    }

    /**
     * Get model instance
     */
    public function getModel(): Model
    {
        return $this->model;
    }
}

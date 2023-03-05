<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Database;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Tomchochola\Laratchi\Http\Requests\FormRequest;

/**
 * @mixin Model
 */
trait ModelTrait
{
    /**
     * ModelTrait constructor.
     *
     * @param array<mixed> $attributes
     */
    final public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * Get qualified key column name.
     */
    public static function getQualifiedKey(): string
    {
        return (new static())->getQualifiedKeyName();
    }

    /**
     * Get qualified key column name.
     */
    public static function getKeyColumn(): string
    {
        return static::getQualifiedKey();
    }

    /**
     * Get qualified route key column name.
     */
    public static function getQualifiedRouteKey(): string
    {
        return (new static())->getQualifiedRouteKeyName();
    }

    /**
     * Get qualified route key column name.
     */
    public static function getRouteKeyColumn(): string
    {
        return static::getQualifiedRouteKey();
    }

    /**
     * Find instance by key.
     *
     * @param (Closure(Builder): void)|null $closure
     */
    public static function findByKey(int|string $key, ?Closure $closure = null): ?static
    {
        $builder = static::query();

        if ($closure !== null) {
            $builder = $builder->tap($closure);
        }

        $instance = $builder->find($key);

        if ($instance === null) {
            return null;
        }

        \assert($instance instanceof static);

        return $instance;
    }

    /**
     * Mandatory find instance by key.
     *
     * @param (Closure(Builder): void)|null $closure
     * @param Closure(): never $onError
     */
    public static function mustFindByKey(int|string $key, ?Closure $closure, Closure $onError): static
    {
        $instance = static::findByKey($key, $closure);

        if ($instance === null) {
            $onError();
        }

        return $instance;
    }

    /**
     * Find instance by route key.
     *
     * @param (Closure(Builder): void)|null $closure
     */
    public static function findByRouteKey(string $key, ?Closure $closure = null): ?static
    {
        $builder = static::query()->where(static::getRouteKeyColumn(), $key);

        if ($closure !== null) {
            $builder = $builder->tap($closure);
        }

        $instance = $builder->first();

        if ($instance === null) {
            return null;
        }

        \assert($instance instanceof static);

        return $instance;
    }

    /**
     * Mandatory find instance by route key.
     *
     * @param (Closure(Builder): void)|null $closure
     * @param Closure(): never $onError
     */
    public static function mustFindByRouteKey(string $key, ?Closure $closure, Closure $onError): static
    {
        $instance = static::findByRouteKey($key, $closure);

        if ($instance === null) {
            $onError();
        }

        return $instance;
    }

    /**
     * Must resolve from request.
     *
     * @param (Closure(Builder): void)|null $closure
     */
    public static function mustResolveFromRequest(FormRequest $request, ?Closure $closure = null): static
    {
        $id = $request->fastInteger('id');

        if ($id !== null) {
            return static::mustFindByKey($id, $closure, static function () use ($request): never {
                $request->throwSingleValidationException(['id'], 'Exists');
            });
        }

        $slug = $request->fastString('slug');

        if ($slug !== null) {
            return static::mustFindByRouteKey($slug, $closure, static function () use ($request): never {
                $request->throwSingleValidationException(['slug'], 'Exists');
            });
        }

        $request->throwSingleValidationException(['id', 'slug'], 'Required');
    }

    /**
     * Create new model.
     *
     * @param array<mixed> $attributes
     * @param (Closure(static): void)|null $closure
     */
    public static function create(array $attributes, ?Closure $closure = null): static
    {
        $model = new static($attributes);

        if ($closure !== null) {
            $closure($model);
        }

        $model->save();

        return $model;
    }

    /**
     * Create new model.
     *
     * @param array<mixed> $attributes
     * @param (Closure(static): void)|null $closure
     */
    public static function mustCreate(array $attributes, ?Closure $closure = null): static
    {
        return static::create($attributes, $closure);
    }

    /**
     * Make new model.
     *
     * @param array<mixed> $attributes
     * @param (Closure(static): void)|null $closure
     */
    public static function make(array $attributes, ?Closure $closure = null): static
    {
        $model = new static($attributes);

        if ($closure !== null) {
            $closure($model);
        }

        return $model;
    }

    /**
     * Make new model.
     *
     * @param array<mixed> $attributes
     * @param (Closure(static): void)|null $closure
     */
    public static function mustMake(array $attributes, ?Closure $closure = null): static
    {
        return static::mustMake($attributes, $closure);
    }

    /**
     * Get clean instance.
     *
     * @param (Closure(Builder): void)|null $closure
     */
    public static function clean(mixed $key, ?Closure $closure = null): static
    {
        $query = (new static())->newQueryWithoutScopes();

        \assert($query instanceof Builder);

        $query->whereKey($key);

        $query->getQuery()->useWritePdo();

        if ($closure !== null) {
            $closure($query);
        }

        $instance = $query->first();

        \assert($instance instanceof static);

        if ($key instanceof Model) {
            $instance->wasRecentlyCreated = $key->wasRecentlyCreated;
        }

        return $instance;
    }

    /**
     * Scope by keys.
     *
     * @param array<mixed> $ids
     */
    public static function scopeKeys(Builder $builder, array $ids): void
    {
        $builder->whereKey($ids);
    }

    /**
     * Scope by route keys.
     *
     * @param array<mixed> $slugs
     */
    public static function scopeRouteKeys(Builder $builder, array $slugs): void
    {
        $builder->getQuery()->whereIn(static::getRouteKeyColumn(), $slugs);
    }

    /**
     * Get qualified column.
     */
    public static function getQualifiedColumn(string $column): string
    {
        return (new static())->qualifyColumn($column);
    }

    /**
     * Get qualified columns.
     *
     * @param array<int, string> $columns
     *
     * @return array<int, string>
     */
    public static function getQualifiedColumns(array $columns): array
    {
        return (new static())->qualifyColumns($columns);
    }

    /**
     * Get the table qualified key name.
     */
    public function getQualifiedRouteKeyName(): string
    {
        return $this->qualifyColumn($this->getRouteKeyName());
    }

    /**
     * Update model.
     *
     * @param array<mixed> $attributes
     * @param (Closure(static): void)|null $closure
     *
     * @return $this
     */
    public function mustUpdate(array $attributes, ?Closure $closure = null): static
    {
        assertNeverIfNot($this->exists, 'model not exists');

        $this->fill($attributes);

        if ($closure !== null) {
            $closure($this);
        }

        if ($this->isDirty()) {
            $this->save();
        }

        return $this;
    }

    /**
     * Save if dirty.
     *
     * @return $this
     */
    public function mustSaveIfDirty(): static
    {
        if ($this->isDirty() || ! $this->exists) {
            $this->save();
        }

        return $this;
    }

    /**
     * Store into database.
     *
     * @return $this
     */
    public function mustSave(): static
    {
        $this->save();

        return $this;
    }

    /**
     * Delete model from database.
     */
    public function mustDelete(): static
    {
        $this->delete();

        return $this;
    }

    /**
     * Get the value of the model's primary key.
     */
    public function getKey(): int|string
    {
        \assert($this->attributeLoaded($this->getKeyName()));

        $value = $this->getAttributeValue($this->getKeyName());

        \assert(\is_int($value) || \is_string($value), 'model key is not int or string');

        return $value;
    }

    /**
     * Get the value of the model's route key.
     */
    public function getRouteKey(): string
    {
        \assert($this->attributeLoaded($this->getRouteKeyName()));

        $value = $this->getAttributeValue($this->getRouteKeyName());

        \assert(\is_scalar($value), 'model route key is string');

        return (string) $value;
    }

    /**
     * Determine if the given attribute is loaded.
     */
    public function attributeLoaded(string $key): bool
    {
        return \array_key_exists($key, $this->attributes);
    }

    /**
     * Get ?int attribute.
     */
    public function int(string $key): ?int
    {
        \assert($this->attributeLoaded($key), "[{$key}] attribute is not loaded");

        $value = $this->getAttributeValue($key);

        \assert($value === null || \is_int($value), "[{$key}] attribute is not int or null");

        return $value;
    }

    /**
     * Get int attribute.
     */
    public function mustInt(string $key): int
    {
        \assert($this->attributeLoaded($key), "[{$key}] attribute is not loaded");

        $value = $this->getAttributeValue($key);

        \assert(\is_int($value), "[{$key}] attribute is not int");

        return $value;
    }

    /**
     * Get ?float attribute.
     */
    public function float(string $key): ?float
    {
        \assert($this->attributeLoaded($key), "[{$key}] attribute is not loaded");

        $value = $this->getAttributeValue($key);

        \assert($value === null || \is_float($value), "[{$key}] attribute is not float or null");

        return $value;
    }

    /**
     * Get float attribute.
     */
    public function mustFloat(string $key): float
    {
        \assert($this->attributeLoaded($key), "[{$key}] attribute is not loaded");

        $value = $this->getAttributeValue($key);

        \assert(\is_float($value), "[{$key}] attribute is not float");

        return $value;
    }

    /**
     * Get ?string attribute.
     */
    public function string(string $key): ?string
    {
        \assert($this->attributeLoaded($key), "[{$key}] attribute is not loaded");

        $value = $this->getAttributeValue($key);

        \assert($value === null || \is_string($value), "[{$key}] attribute is not string or null");

        return $value;
    }

    /**
     * Get string attribute.
     */
    public function mustString(string $key): string
    {
        \assert($this->attributeLoaded($key), "[{$key}] attribute is not loaded");

        $value = $this->getAttributeValue($key);

        \assert(\is_string($value), "[{$key}] attribute is not string");

        return $value;
    }

    /**
     * Get ?bool attribute.
     */
    public function bool(string $key): ?bool
    {
        \assert($this->attributeLoaded($key), "[{$key}] attribute is not loaded");

        $value = $this->getAttributeValue($key);

        \assert($value === null || \is_bool($value), "[{$key}] attribute is not bool or null");

        return $value;
    }

    /**
     * Get bool attribute.
     */
    public function mustBool(string $key): bool
    {
        \assert($this->attributeLoaded($key), "[{$key}] attribute is not loaded");

        $value = $this->getAttributeValue($key);

        \assert(\is_bool($value), "[{$key}] attribute is not bool");

        return $value;
    }

    /**
     * Get ?array attribute.
     *
     * @return array<mixed>|null
     */
    public function array(string $key): ?array
    {
        \assert($this->attributeLoaded($key), "[{$key}] attribute is not loaded");

        $value = $this->getAttributeValue($key);

        \assert($value === null || \is_array($value), "[{$key}] attribute is not array or null");

        return $value;
    }

    /**
     * Get array attribute.
     *
     * @return array<mixed>
     */
    public function mustArray(string $key): array
    {
        \assert($this->attributeLoaded($key), "[{$key}] attribute is not loaded");

        $value = $this->getAttributeValue($key);

        \assert(\is_array($value), "[{$key}] attribute is not array");

        return $value;
    }

    /**
     * Get ?object attribute.
     */
    public function object(string $key): ?object
    {
        \assert($this->attributeLoaded($key), "[{$key}] attribute is not loaded");

        $value = $this->getAttributeValue($key);

        \assert($value === null || \is_object($value), "[{$key}] attribute is not object or null");

        return $value;
    }

    /**
     * Get object attribute.
     */
    public function mustObject(string $key): object
    {
        \assert($this->attributeLoaded($key), "[{$key}] attribute is not loaded");

        $value = $this->getAttributeValue($key);

        \assert(\is_object($value), "[{$key}] attribute is not object");

        return $value;
    }

    /**
     * Get ?Carbon attribute.
     */
    public function carbon(string $key): ?Carbon
    {
        \assert($this->attributeLoaded($key), "[{$key}] attribute is not loaded");

        $value = $this->getAttributeValue($key);

        \assert($value === null || $value instanceof Carbon, "[{$key}] attribute is not Carbon or null");

        return $value;
    }

    /**
     * Get Carbon attribute.
     */
    public function mustCarbon(string $key): Carbon
    {
        \assert($this->attributeLoaded($key), "[{$key}] attribute is not loaded");

        $value = $this->getAttributeValue($key);

        \assert($value instanceof Carbon, "[{$key}] attribute is not Carbon");

        return $value;
    }

    /**
     * Resolve relationship.
     *
     * @template T of Model
     *
     * @param class-string<T> $type
     *
     * @return ?T
     */
    public function relation(string $key, string $type): ?Model
    {
        \assert($this->relationLoaded($key), "[{$key}] relationship is not loaded");

        $value = $this->getRelationValue($key);

        \assert($value === null || $value instanceof $type, "[{$key}] relationship is not of type [{$type}]");

        return $value;
    }

    /**
     * Must resolve relationship.
     *
     * @template T of Model
     *
     * @param class-string<T> $type
     *
     * @return T
     */
    public function mustRelation(string $key, string $type): Model
    {
        \assert($this->relationLoaded($key), "[{$key}] relationship is not loaded");

        $value = $this->getRelationValue($key);

        \assert($value instanceof $type, "[{$key}] relationship is not of type [{$type}]");

        return $value;
    }

    /**
     * Must resolve relationship.
     *
     * @template T of Model
     *
     * @param class-string<T> $type
     *
     * @return Collection<array-key, T>
     */
    public function mustRelations(string $key, string $type): Collection
    {
        \assert($this->relationLoaded($key), "[{$key}] relationship is not loaded");

        $value = $this->getRelationValue($key);

        \assert($value instanceof Collection, "[{$key}] relationship is not of type [{$type}] and is not collection");

        return $value;
    }

    /**
     * Created at getter.
     */
    public function getCreatedAt(): Carbon
    {
        return $this->mustCarbon('created_at');
    }

    /**
     * Updated at getter.
     */
    public function getUpdatedAt(): Carbon
    {
        return $this->mustCarbon('updated_at');
    }

    /**
     * @inheritDoc
     *
     * @param array<mixed> $options
     */
    public function save(array $options = []): bool
    {
        $ok = parent::save($options);

        assertNeverIfNot($ok, 'model not saved correctly');

        return true;
    }

    /**
     * @inheritDoc
     */
    public function delete(): bool
    {
        assertNeverIfNot($this->exists, 'model not exists');

        $ok = parent::delete();

        assertNeverIfNot($ok === true, 'model not deleted correctly');

        return true;
    }

    /**
     * @inheritDoc
     *
     * @param array<mixed> $attributes
     * @param array<mixed> $options
     */
    public function update(array $attributes = [], array $options = []): bool
    {
        assertNeverIfNot($this->exists, 'model not exists');

        $this->fill($attributes);

        if ($this->isDirty()) {
            $this->save();
        }

        return true;
    }

    /**
     * @inheritDoc
     *
     * @param array<mixed> $attributes
     * @param array<mixed> $options
     */
    public function updateQuietly(array $attributes = [], array $options = []): bool
    {
        assertNeverIfNot($this->exists, 'model not exists');

        $this->fill($attributes);

        if ($this->isDirty()) {
            $this->saveQuietly();
        }

        return true;
    }

    /**
     * @inheritDoc
     *
     * @param array<mixed> $attributes
     * @param array<mixed> $options
     */
    public function updateOrFail(array $attributes = [], array $options = []): bool
    {
        assertNeverIfNot($this->exists, 'model not exists');

        $this->fill($attributes);

        if ($this->isDirty()) {
            $this->saveOrFail();
        }

        return true;
    }
}

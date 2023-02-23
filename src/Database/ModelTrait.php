<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Database;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Carbon;

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
        return (new static())->getQualifiedKeyName();
    }

    /**
     * Get qualified route key column name.
     */
    public static function getRouteKeyColumn(): string
    {
        return (new static())->getQualifiedRouteKeyName();
    }

    /**
     * Get qualified route key column name.
     */
    public static function getQualifiedRouteKey(): string
    {
        return (new static())->getQualifiedRouteKeyName();
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
     * @param (Closure(): never)|null $onError
     */
    public static function mustFindByKey(int|string $key, ?Closure $closure = null, ?Closure $onError = null): static
    {
        $instance = static::findByKey($key, $closure);

        if ($instance === null) {
            if ($onError !== null) {
                $onError();
            }

            throw (new ModelNotFoundException())->setModel(static::class, $key);
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
     * @param (Closure(): never)|null $onError
     */
    public static function mustFindByRouteKey(string $key, ?Closure $closure = null, ?Closure $onError = null): static
    {
        $instance = static::findByRouteKey($key, $closure);

        if ($instance === null) {
            if ($onError !== null) {
                $onError();
            }

            throw (new ModelNotFoundException())->setModel(static::class, $key);
        }

        return $instance;
    }

    /**
     * Create new model.
     *
     * @param array<mixed> $attributes
     * @param (Closure(static): void)|null $closure
     */
    public static function mustCreate(array $attributes, ?Closure $closure = null): static
    {
        $model = new static($attributes);

        if ($closure !== null) {
            $closure($model);
        }

        $model->mustSave();

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
        $model = new static($attributes);

        if ($closure !== null) {
            $closure($model);
        }

        return $model;
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

        $instance = $query->firstOrFail();

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
        $this->fill($attributes);

        if ($closure !== null) {
            $closure($this);
        }

        return $this->mustSaveIfDirty();
    }

    /**
     * Save if dirty.
     *
     * @return $this
     */
    public function mustSaveIfDirty(): static
    {
        if ($this->isDirty()) {
            return $this->mustSave();
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
        $ok = $this->save();

        \assert($ok, 'model not saved correctly');

        return $this;
    }

    /**
     * Delete model from database.
     */
    public function mustDelete(): void
    {
        $ok = $this->delete();

        \assert((bool) $ok, 'model not deleted correctly');
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

        if ($value === null) {
            return null;
        }

        \assert(\is_int($value), "[{$key}] attribute is not int or null");

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

        if ($value === null) {
            return null;
        }

        \assert(\is_float($value), "[{$key}] attribute is not float or null");

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

        if ($value === null) {
            return null;
        }

        \assert(\is_string($value), "[{$key}] attribute is not string or null");

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

        if ($value === null) {
            return null;
        }

        \assert(\is_bool($value), "[{$key}] attribute is not bool or null");

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

        if ($value === null) {
            return null;
        }

        \assert(\is_array($value), "[{$key}] attribute is not array or null");

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

        if ($value === null) {
            return null;
        }

        \assert(\is_object($value), "[{$key}] attribute is not object or null");

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

        if ($value === null) {
            return null;
        }

        \assert($value instanceof Carbon, "[{$key}] attribute is not Carbon or null");

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

        if ($value === null) {
            return null;
        }

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
}

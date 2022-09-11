<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Database;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @mixin Model
 */
trait ModelTrait
{
    /**
     * @inheritDoc
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
    public static function getKeyColumn(): string
    {
        $instance = new static();

        return $instance->getQualifiedKeyName();
    }

    /**
     * Get qualified route key column name.
     */
    public static function getRouteKeyColumn(): string
    {
        $instance = new static();

        return $instance->qualifyColumn($instance->getRouteKeyName());
    }

    /**
     * Find instance by key.
     *
     * @param (Closure(Builder): void)|null $closure
     */
    public static function findByKey(int|string $key, ?Closure $closure = null): ?static
    {
        $instance = static::query()->tap($closure ?? static function (): void {
        })->find($key);

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
     */
    public static function mustFindByKey(int|string $key, ?Closure $closure = null): static
    {
        $instance = static::query()->tap($closure ?? static function (): void {
        })->findOrFail($key);

        \assert($instance instanceof static);

        return $instance;
    }

    /**
     * Find instance by route key.
     *
     * @param (Closure(Builder): void)|null $closure
     */
    public static function findByRouteKey(int|string $key, ?Closure $closure = null): ?static
    {
        $instance = static::query()->tap($closure ?? static function (): void {
        })->where(static::getRouteKeyColumn(), $key)->first();

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
     */
    public static function mustFindByRouteKey(int|string $key, ?Closure $closure = null): static
    {
        $instance = static::query()->tap($closure ?? static function (): void {
        })->where(static::getRouteKeyColumn(), $key)->firstOrFail();

        \assert($instance instanceof static);

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
     * @inheritDoc
     */
    public function getKey(): int|string
    {
        \assert($this->attributeLoaded($this->getKeyName()));

        $value = parent::getKey();

        \assert(\is_int($value) || \is_string($value), 'model key is not int or string');

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function getRouteKey(): int|string
    {
        \assert($this->attributeLoaded($this->getRouteKeyName()));

        $value = parent::getRouteKey();

        \assert(\is_int($value) || \is_string($value), 'model route key is not int or string');

        return $value;
    }

    /**
     * Determine if the given attribute is loaded.
     */
    public function attributeLoaded(string $key): bool
    {
        return \array_key_exists($key, $this->attributes);
    }

    /**
     * Get int key.
     */
    public function getIntKey(): int
    {
        return $this->mustInt($this->getKeyName());
    }

    /**
     * Get int route key.
     */
    public function getIntRouteKey(): int
    {
        return $this->mustInt($this->getRouteKeyName());
    }

    /**
     * Get string key.
     */
    public function getStringKey(): string
    {
        return $this->mustString($this->getKeyName());
    }

    /**
     * Get string route key.
     */
    public function getStringRouteKey(): string
    {
        return $this->mustString($this->getRouteKeyName());
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
     * @return array<mixed>
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
        \assert($this->relationLoaded($key), "[{$key}] relationsip is not loaded");

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
        \assert($this->relationLoaded($key), "[{$key}] relationsip is not loaded");

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
        \assert($this->relationLoaded($key), "[{$key}] relationsip is not loaded");

        $value = $this->getRelationValue($key);

        \assert($value instanceof Collection, "[{$key}] relationship is not of type [{$type}] and is not collection");

        return $value;
    }

    /**
     * Id getter.
     */
    public function getId(): int
    {
        return $this->mustInt('id');
    }

    /**
     * Created at getter.
     */
    public function getCreatedAt(): ?Carbon
    {
        return $this->carbon('created_at');
    }

    /**
     * Updated at getter.
     */
    public function getUpdatedAt(): ?Carbon
    {
        return $this->carbon('updated_at');
    }
}

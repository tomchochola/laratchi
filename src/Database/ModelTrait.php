<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Database;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

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

        \assert($ok);

        return $this;
    }

    /**
     * Delete model from database.
     */
    public function mustDelete(): void
    {
        $ok = $this->delete();

        \assert((bool) $ok);
    }

    /**
     * @inheritDoc
     */
    public function getKey(): int|string
    {
        $value = parent::getKey();

        \assert(\is_int($value) || \is_string($value));

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function getRouteKey(): int|string
    {
        $value = parent::getRouteKey();

        \assert(\is_int($value) || \is_string($value));

        return $value;
    }

    /**
     * Determine if the given attribute is loaded.
     */
    public function attributeLoaded(string $key): bool
    {
        return \array_key_exists($key, $this->attributes);
    }
}

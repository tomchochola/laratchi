<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Database;

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
     */
    public static function findByKey(int|string $key): ?static
    {
        $instance = static::query()->find($key);

        if ($instance === null) {
            return null;
        }

        \assert($instance instanceof static);

        return $instance;
    }

    /**
     * Mandatory find instance by key.
     */
    public static function mustFindByKey(int|string $key): static
    {
        $instance = static::query()->findOrFail($key);

        \assert($instance instanceof static);

        return $instance;
    }

    /**
     * Find instance by route key.
     */
    public static function findByRouteKey(int|string $key): ?static
    {
        $instance = static::query()->where(static::getRouteKeyColumn(), $key)->first();

        if ($instance === null) {
            return null;
        }

        \assert($instance instanceof static);

        return $instance;
    }

    /**
     * Mandatory find instance by route key.
     */
    public static function mustFindByRouteKey(int|string $key): static
    {
        $instance = static::query()->where(static::getRouteKeyColumn(), $key)->firstOrFail();

        \assert($instance instanceof static);

        return $instance;
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

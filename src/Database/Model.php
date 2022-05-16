<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Database;

use Illuminate\Database\Eloquent\Model as IlluminateModel;
use Illuminate\Support\Carbon;

/**
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class Model extends IlluminateModel
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
     * @inheritDoc
     */
    public function getKey(): int
    {
        $value = parent::getKey();

        \assert(\is_int($value));

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function getRouteKey(): string
    {
        $value = parent::getRouteKey();

        \assert(\is_scalar($value));

        return (string) $value;
    }
}

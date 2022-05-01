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

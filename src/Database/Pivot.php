<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Database;

use Illuminate\Database\Eloquent\Relations\Pivot as IlluminatePivot;
use Illuminate\Support\Carbon;

/**
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class Pivot extends IlluminatePivot
{
    /**
     * @inheritDoc
     */
    public $incrementing = true;

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
    public function getRouteKey(): string
    {
        $value = parent::getRouteKey();

        \assert(\is_scalar($value));

        return (string) $value;
    }
}

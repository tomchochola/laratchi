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
    use ModelTrait;

    /**
     * @inheritDoc
     */
    public $incrementing = true;
}

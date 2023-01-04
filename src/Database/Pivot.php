<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Database;

use Illuminate\Database\Eloquent\Relations\Pivot as IlluminatePivot;

class Pivot extends IlluminatePivot
{
    use ModelTrait;
    use TypedModelTrait {
        TypedModelTrait::getKey insteadof ModelTrait;
    }

    /**
     * @inheritDoc
     */
    public $incrementing = true;

    /**
     * @inheritDoc
     */
    public $preventsLazyLoading = true;
}

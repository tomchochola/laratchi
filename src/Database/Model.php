<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Database;

use Illuminate\Database\Eloquent\Model as IlluminateModel;

class Model extends IlluminateModel
{
    use ModelTrait;

    /**
     * @inheritDoc
     */
    public $preventsLazyLoading = true;
}

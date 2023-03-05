<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Database;

use Illuminate\Database\Eloquent\Relations\Pivot as IlluminatePivot;

class Pivot extends IlluminatePivot
{
    use IntModelTrait {
        IntModelTrait::getKey insteadof ModelTrait;
        IntModelTrait::findByKey insteadof ModelTrait;
        IntModelTrait::mustFindByKey insteadof ModelTrait;
    }
    use ModelTrait;

    /**
     * @inheritDoc
     */
    public $incrementing = true;

    /**
     * @inheritDoc
     */
    public $preventsLazyLoading = true;

    /**
     * @inheritDoc
     */
    public function delete(): int
    {
        assertNeverIfNot($this->exists, 'model not exists');

        $ok = parent::delete();

        assertNeverIfNot($ok === 1, 'model not deleted correctly');

        return $ok;
    }
}

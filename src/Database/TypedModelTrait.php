<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Database;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Model
 */
trait TypedModelTrait
{
    /**
     * @inheritDoc
     */
    public function getKey(): int
    {
        \assert($this->attributeLoaded($this->getKeyName()));

        $value = $this->getAttributeValue($this->getKeyName());

        \assert(\is_int($value), 'model key is not int');

        return $value;
    }
}

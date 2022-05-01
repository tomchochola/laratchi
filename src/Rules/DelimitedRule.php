<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Rules;

use Spatie\ValidationRules\Rules\Delimited;

class DelimitedRule extends Delimited
{
    /**
     * @inheritDoc
     */
    public function passes(mixed $attribute, mixed $value): bool
    {
        if (! \is_string($value)) {
            $this->message = mustTransString('validation.string');

            return false;
        }

        return parent::passes($attribute, $value);
    }
}

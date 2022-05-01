<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Rules;

use Illuminate\Validation\Rules\RequiredIf;

class ProhibitedIfRule extends RequiredIf
{
    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        if (\is_callable($this->condition)) {
            return ($this->condition)() ? 'prohibited' : '';
        }

        return $this->condition ? 'prohibited' : '';
    }
}

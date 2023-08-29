<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Validation\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Pagination\Cursor;

class CursorRule implements ValidationRule
{
    /**
     * @inheritDoc
     */
    public function validate(mixed $attribute, mixed $value, Closure $fail): void
    {
        if (!\is_string($value)) {
            $fail(\mustTransString('validation.invalid'));

            return;
        }

        if (Cursor::fromEncoded($value) !== null) {
            return;
        }

        $fail(\mustTransString('validation.invalid'));
    }
}

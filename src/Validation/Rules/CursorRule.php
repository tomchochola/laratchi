<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Validation\Rules;

use Illuminate\Contracts\Validation\Rule as RuleContract;
use Illuminate\Pagination\Cursor;

class CursorRule implements RuleContract
{
    /**
     * @inheritDoc
     */
    public function passes(mixed $attribute, mixed $value): bool
    {
        if (! \is_string($value)) {
            return false;
        }

        return Cursor::fromEncoded($value) !== null;
    }

    /**
     * @inheritDoc
     *
     * @return string|array<int, string>
     */
    public function message(): string|array
    {
        return mustTransString('validation.invalid');
    }
}

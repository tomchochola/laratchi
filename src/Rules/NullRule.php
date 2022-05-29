<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Rules;

use Illuminate\Contracts\Validation\Rule as RuleContract;

class NullRule implements RuleContract
{
    /**
     * @inheritDoc
     */
    public function passes(mixed $attribute, mixed $value): bool
    {
        return $value === null;
    }

    /**
     * @inheritDoc
     *
     * @return string|array<int, string>
     */
    public function message(): string|array
    {
        return mustTransString('validation.prohibited');
    }
}

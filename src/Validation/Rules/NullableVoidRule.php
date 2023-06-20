<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Validation\Rules;

use Closure;
use Illuminate\Contracts\Validation\ImplicitRule as RuleContract;

class NullableVoidRule implements RuleContract
{
    /**
     * Create a new rule instance.
     *
     * @param Closure(mixed, mixed=): void $callback
     */
    public function __construct(protected Closure $callback)
    {
    }

    /**
     * @inheritDoc
     */
    public function passes(mixed $attribute, mixed $value): bool
    {
        ($this->callback)($value, $attribute);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function message(): never
    {
        assertNever();
    }
}

<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Rules;

use Closure;
use Illuminate\Contracts\Validation\Rule as RuleContract;

class CallbackRule implements RuleContract
{
    /**
     * Create a new rule instance.
     *
     * @param Closure(mixed, mixed=): bool $callback
     */
    public function __construct(protected Closure $callback, protected string $message)
    {
    }

    /**
     * @inheritDoc
     */
    public function passes(mixed $attribute, mixed $value): bool
    {
        return ($this->callback)($value, $attribute);
    }

    /**
     * @inheritDoc
     *
     * @return string|array<int, string>
     */
    public function message(): string|array
    {
        return mustTransString($this->message);
    }
}

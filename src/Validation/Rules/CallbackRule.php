<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Validation\Rules;

use Closure;
use Illuminate\Contracts\Validation\Rule as RuleContract;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class CallbackRule implements RuleContract
{
    /**
     * Create a new rule instance.
     *
     * @param Closure(mixed, mixed=): (bool|int|string) $callback
     */
    public function __construct(protected Closure $callback, protected int|string $message = 'validation.invalid')
    {
    }

    /**
     * @inheritDoc
     */
    public function passes(mixed $attribute, mixed $value): bool
    {
        $passes = ($this->callback)($value, $attribute);

        if (\is_string($passes)) {
            $this->message = $passes;

            return false;
        }

        if (\is_int($passes)) {
            throw new UnprocessableEntityHttpException('', null, $passes);
        }

        if (! $passes && \is_int($this->message)) {
            throw new UnprocessableEntityHttpException('', null, $this->message);
        }

        return $passes;
    }

    /**
     * @inheritDoc
     *
     * @return string|array<int, string>
     */
    public function message(): string|array
    {
        return mustTransString((string) $this->message);
    }
}

<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Validation;

use Illuminate\Support\ValidatedInput as IlluminateValidatedInput;

class ValidatedInput extends IlluminateValidatedInput
{
    /**
     * String resolver.
     */
    public function string(string $key, ?string $default = null, bool $trim = true): ?string
    {
        $value = $this->resolve($key, $default);

        if ($value === null) {
            return null;
        }

        $value = \filter_var($value);

        \assert($value !== false);

        if ($trim) {
            return \trim($value);
        }

        return $value;
    }

    /**
     * Mandatory string resolver.
     */
    public function mustString(string $key, ?string $default = null, bool $trim = true): string
    {
        $value = $this->string($key, $default, $trim);

        \assert($value !== null);

        return $value;
    }

    /**
     * Bool resolver.
     */
    public function bool(string $key, ?bool $default = null): ?bool
    {
        $value = $this->resolve($key, $default);

        if ($value === null) {
            return null;
        }

        return \filter_var($value, \FILTER_VALIDATE_BOOL, \FILTER_NULL_ON_FAILURE);
    }

    /**
     * Mandatory bool resolver.
     */
    public function mustBool(string $key, ?bool $default = null): bool
    {
        $value = $this->bool($key, $default);

        \assert($value !== null);

        return $value;
    }

    /**
     * Int resolver.
     */
    public function int(string $key, ?int $default = null): ?int
    {
        $value = $this->resolve($key, $default);

        if ($value === null) {
            return null;
        }

        $value = \filter_var($value, \FILTER_VALIDATE_INT);

        \assert($value !== false);

        return $value;
    }

    /**
     * Mandatory int resolver.
     */
    public function mustInt(string $key, ?int $default = null): int
    {
        $value = $this->int($key, $default);

        \assert($value !== null);

        return $value;
    }

    /**
     * Float resolver.
     */
    public function float(string $key, ?float $default = null): ?float
    {
        $value = $this->resolve($key, $default);

        if ($value === null) {
            return null;
        }

        $value = \filter_var($value, \FILTER_VALIDATE_FLOAT);

        \assert($value !== false);

        return $value;
    }

    /**
     * Mandatory float resolver.
     */
    public function mustFloat(string $key, ?float $default = null): float
    {
        $value = $this->float($key, $default);

        \assert($value !== null);

        return $value;
    }

    /**
     * Resolve value from data array.
     */
    protected function resolve(string $key, mixed $default = null): mixed
    {
        $value = $this[$key] ?? $default;

        if (\is_string($value) && \trim($value) === '') {
            return null;
        }

        return $value;
    }
}

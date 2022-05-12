<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Validation;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\ValidatedInput as IlluminateValidatedInput;

class ValidatedInput extends IlluminateValidatedInput
{
    /**
     * String resolver.
     */
    public function string(string $key, ?string $default = null, bool $trim = true): ?string
    {
        $value = $this->get($key, $default);

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
        $value = $this->get($key, $default);

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
        $value = $this->get($key, $default);

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
        $value = $this->get($key, $default);

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
     * File resolver.
     */
    public function file(string $key, ?UploadedFile $default = null): ?UploadedFile
    {
        $value = $this->get($key, $default);

        if ($value === null) {
            return null;
        }

        \assert($value instanceof UploadedFile);

        return $value;
    }

    /**
     * Mandatory file resolver.
     */
    public function mustFile(string $key, ?UploadedFile $default = null): UploadedFile
    {
        $value = $this->file($key, $default);

        \assert($value !== null);

        return $value;
    }

    /**
     * Array resolver.
     *
     * @param array<mixed>|null $default
     *
     * @return array<mixed>|null
     */
    public function array(string $key, ?array $default = null): ?array
    {
        $value = $this->get($key, $default);

        if ($value === null) {
            return null;
        }

        \assert(\is_array($value));

        return $value;
    }

    /**
     * Mandatory array resolver.
     *
     * @param array<mixed>|null $default
     *
     * @return array<mixed>
     */
    public function mustArray(string $key, ?array $default = null): array
    {
        $value = $this->array($key, $default);

        \assert($value !== null);

        return $value;
    }

    /**
     * Resolve value from data array.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $value = Arr::get($this->input, $key, $default);

        if (\is_string($value) && \trim($value) === '') {
            return null;
        }

        return $value;
    }
}

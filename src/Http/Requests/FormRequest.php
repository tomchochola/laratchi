<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest as IlluminateFormRequest;
use Illuminate\Routing\Route;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;
use Tomchochola\Laratchi\Validation\ValidatedInput;

class FormRequest extends IlluminateFormRequest
{
    /**
     * Validated input cache.
     */
    protected ?ValidatedInput $validatedInput = null;

    /**
     * All input cache.
     */
    protected ?ValidatedInput $allInput = null;

    /**
     * Query parameters cache.
     */
    protected ?ValidatedInput $queryParameters = null;

    /**
     * Get a validated input container for the validated input.
     */
    public function validatedInput(): ValidatedInput
    {
        if ($this->validatedInput !== null) {
            return $this->validatedInput;
        }

        $data = $this->validated();

        \assert(\is_array($data));

        return $this->validatedInput = new ValidatedInput($data);
    }

    /**
     * Get a all input container for the all input.
     */
    public function allInput(): ValidatedInput
    {
        if ($this->allInput !== null) {
            return $this->allInput;
        }

        return $this->allInput = new ValidatedInput($this->all());
    }

    /**
     * Get query parameters.
     */
    public function queryParameters(): ValidatedInput
    {
        if ($this->queryParameters !== null) {
            return $this->queryParameters;
        }

        $data = $this->query();

        \assert(\is_array($data));

        return $this->queryParameters = new ValidatedInput($data);
    }

    /**
     * Slug getter.
     */
    public function slug(string $key = 'slug', ?string $default = null): string
    {
        $route = $this->route();

        \assert($route instanceof Route);

        $value = $route->parameter($key, $default);

        if (\is_string($value)) {
            return $value;
        }

        $value = \filter_var($value);

        if ($value === false) {
            return '';
        }

        return $value;
    }

    /**
     * Throw validation exception.
     *
     * @param array<string, array<string, array<string>>> $errors
     */
    public function throwValidationException(array $errors, ?int $status = null): never
    {
        $validator = $this->getValidatorInstance();

        \assert($validator instanceof Validator);

        foreach ($errors as $field => $exceptions) {
            foreach ($exceptions as $exception => $params) {
                $validator->addFailure($field, $exception, $params);
            }
        }

        $exception = new ValidationException($validator);

        if ($status !== null) {
            $exception->status($status);
        }

        throw $exception;
    }

    /**
     * Throw throttle validation error.
     *
     * @param array<array-key> $keys
     */
    public function throwThrottleValidationError(array $keys, int $seconds, string $rule = 'throttled', ?int $status = null): never
    {
        $validator = $this->getValidatorInstance();

        \assert($validator instanceof Validator);

        foreach ($keys as $key) {
            $validator->addFailure($key, $rule, [
                'seconds' => (string) $seconds,
                'minutes' => (string) \ceil($seconds / 60),
            ]);
        }

        $exception = new ValidationException($validator);

        if ($status !== null) {
            $exception->status($status);
        }

        throw $exception;
    }

    /**
     * Throw unique validation exception.
     *
     * @param array<string> $keys
     */
    public function throwUniqueValidationException(array $keys, ?int $status = null): never
    {
        $this->throwSingleValidationException($keys, 'Unique', $status);
    }

    /**
     * Throw single validation exception.
     *
     * @param array<string> $keys
     */
    public function throwSingleValidationException(array $keys, string $rule, ?int $status = null): never
    {
        $this->throwValidationException(\array_map(static fn (): array => [$rule => []], \array_flip($keys)), $status);
    }

    /**
     * @inheritDoc
     */
    public function string(mixed $key, mixed $default = null): Stringable
    {
        $value = $this->input($key, $default);

        if ($value instanceof Stringable) {
            return $value;
        }

        if (\is_string($value)) {
            return Str::of($value);
        }

        $value = \filter_var($value);

        if ($value === false) {
            return Str::of('');
        }

        return Str::of($value);
    }

    /**
     * @inheritDoc
     */
    public function integer(mixed $key, mixed $default = 0): int
    {
        return $this->mustFastInteger($key, $default);
    }

    /**
     * @inheritDoc
     */
    public function float(mixed $key, mixed $default = 0.0): float
    {
        return $this->mustFastFloat($key, $default);
    }

    /**
     * @inheritDoc
     */
    public function date(mixed $key, mixed $format = null, mixed $tz = null): Carbon
    {
        return $this->mustFastDate($key, null, $format, $tz);
    }

    /**
     * @inheritDoc
     *
     * @param array<mixed>|string|null $key
     *
     * @return Collection<array-key, mixed>
     */
    public function collect(mixed $key = null): Collection
    {
        if (\is_array($key)) {
            return collect($this->only($key));
        }

        $value = $this->input($key);

        if (! \is_array($value)) {
            return collect([]);
        }

        return collect($value);
    }

    /**
     * Resolve varchar.
     */
    public function varchar(string $key, ?string $default = null): string
    {
        return $this->mustFastString($key, $default);
    }

    /**
     * Retrieve string from request.
     */
    public function fastString(string $key, ?string $default = null): ?string
    {
        $value = $this->input($key, $default);

        if ($value === null || \is_string($value)) {
            return $value;
        }

        $value = \filter_var($value);

        if ($value === false) {
            return null;
        }

        return $value;
    }

    /**
     * Retrieve int from request.
     */
    public function fastInteger(string $key, ?int $default = null): ?int
    {
        $value = $this->input($key, $default);

        if ($value === null || \is_int($value)) {
            return $value;
        }

        $value = \filter_var($value, \FILTER_VALIDATE_INT);

        if ($value === false) {
            return null;
        }

        return $value;
    }

    /**
     * Retrieve float from request.
     */
    public function fastFloat(string $key, ?float $default = null): ?float
    {
        $value = $this->input($key, $default);

        if ($value === null || \is_float($value)) {
            return $value;
        }

        $value = \filter_var($value, \FILTER_VALIDATE_FLOAT);

        if ($value === false) {
            return null;
        }

        return $value;
    }

    /**
     * Retrieve boolean from request.
     */
    public function fastBoolean(string $key, ?bool $default = null): ?bool
    {
        $value = $this->input($key, $default);

        if ($value === null || \is_bool($value)) {
            return $value;
        }

        return \filter_var($value, \FILTER_VALIDATE_BOOL, \FILTER_NULL_ON_FAILURE);
    }

    /**
     * Retrieve date from request.
     */
    public function fastDate(string $key, ?Carbon $default = null, ?string $format = null, ?string $tz = null): ?Carbon
    {
        $value = $this->get($key, $default);

        if ($value === null) {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value;
        }

        $value = \filter_var($value);

        if ($value === false || $value === '') {
            return null;
        }

        if ($format === null) {
            return resolveDate()->parse($value, $tz);
        }

        $value = resolveDate()->createFromFormat($format, $value, $tz);

        if ($value === false) {
            return null;
        }

        return $value;
    }

    /**
     * Mandatory retrieve string from request.
     */
    public function mustFastString(string $key, ?string $default = null): string
    {
        return $this->fastString($key, $default) ?? '';
    }

    /**
     * Mandatory retrieve int from request.
     */
    public function mustFastInteger(string $key, ?int $default = null): int
    {
        return $this->fastInteger($key, $default) ?? 0;
    }

    /**
     * Mandatory retrieve float from request.
     */
    public function mustFastFloat(string $key, ?float $default = null): float
    {
        return $this->fastFloat($key, $default) ?? 0;
    }

    /**
     * Mandatory retrieve boolean from request.
     */
    public function mustFastBoolean(string $key, ?bool $default = null): bool
    {
        return $this->fastBoolean($key, $default) ?? false;
    }

    /**
     * Mandatory retrieve date from request.
     */
    public function mustFastDate(string $key, ?Carbon $default = null, ?string $format = null, ?string $tz = null): Carbon
    {
        return $this->fastDate($key, $default, $format, $tz) ?? resolveDate()->now();
    }
}

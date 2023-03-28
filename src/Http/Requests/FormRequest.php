<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest as IlluminateFormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Route;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;
use Tomchochola\Laratchi\Validation\AllInput;
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
    protected ?AllInput $allInput = null;

    /**
     * Route parameters cache.
     */
    protected ?AllInput $routeParameters = null;

    /**
     * Create validation exception.
     *
     * @param array<string, array<string, array<string>>> $errors
     */
    public static function createValidationException(?Validator $validator, array $errors, ?int $status = null): ValidationException
    {
        $validator ??= resolveValidatorFactory()->make([], []);

        foreach ($errors as $field => $exceptions) {
            foreach ($exceptions as $exception => $params) {
                $validator->addFailure($field, $exception, $params);
            }
        }

        $exception = new ValidationException($validator);

        if ($status !== null) {
            $exception->status($status);
        }

        return $exception;
    }

    /**
     * Create throttle validation exception.
     *
     * @param array<array-key> $keys
     */
    public static function createThrottleValidationException(?Validator $validator, array $keys, int $seconds, string $rule = 'throttled', ?int $status = null): ValidationException
    {
        $validator ??= resolveValidatorFactory()->make([], []);

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

        return $exception;
    }

    /**
     * Create unique validation exception.
     *
     * @param array<string> $keys
     */
    public static function createUniqueValidationException(?Validator $validator, array $keys, ?int $status = null): ValidationException
    {
        $validator ??= resolveValidatorFactory()->make([], []);

        return static::createSingleValidationException($validator, $keys, 'Unique', $status);
    }

    /**
     * Create single validation exception.
     *
     * @param array<string> $keys
     */
    public static function createSingleValidationException(?Validator $validator, array $keys, string $rule, ?int $status = null): ValidationException
    {
        $validator ??= resolveValidatorFactory()->make([], []);

        return static::createValidationException($validator, \array_map(static fn (): array => [$rule => []], \array_flip($keys)), $status);
    }

    /**
     * Get a validated input container for the validated input.
     */
    public function validatedInput(): ValidatedInput
    {
        if ($this->validatedInput !== null) {
            return $this->validatedInput;
        }

        return $this->validatedInput = new ValidatedInput($this->validator->validated());
    }

    /**
     * Get a all input container for the all input.
     */
    public function allInput(): AllInput
    {
        if ($this->allInput !== null) {
            return $this->allInput;
        }

        return $this->allInput = new AllInput($this->all());
    }

    /**
     * Get route parameters.
     */
    public function routeParameters(): AllInput
    {
        if ($this->routeParameters !== null) {
            return $this->routeParameters;
        }

        $route = $this->route();

        \assert($route instanceof Route);

        return $this->routeParameters = new AllInput($route->parameters());
    }

    /**
     * Slug getter.
     */
    public function slug(): string
    {
        return $this->routeParameters()->mustString('slug');
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

        throw static::createValidationException($validator, $errors, $status);
    }

    /**
     * Throw throttle validation exception.
     *
     * @param array<array-key> $keys
     */
    public function throwThrottleValidationError(array $keys, int $seconds, string $rule = 'throttled', ?int $status = null): never
    {
        $validator = $this->getValidatorInstance();

        \assert($validator instanceof Validator);

        throw static::createThrottleValidationException($validator, $keys, $seconds, $rule, $status);
    }

    /**
     * Throw unique validation exception.
     *
     * @param array<string> $keys
     */
    public function throwUniqueValidationException(array $keys, ?int $status = null): never
    {
        $validator = $this->getValidatorInstance();

        \assert($validator instanceof Validator);

        throw static::createUniqueValidationException($validator, $keys, $status);
    }

    /**
     * Throw single validation exception.
     *
     * @param array<string> $keys
     */
    public function throwSingleValidationException(array $keys, string $rule, ?int $status = null): never
    {
        $validator = $this->getValidatorInstance();

        \assert($validator instanceof Validator);

        throw static::createSingleValidationException($validator, $keys, $rule, $status);
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

        if ($value === null) {
            return Str::of('');
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
        return $this->allInput()->mustInt($key, $default);
    }

    /**
     * @inheritDoc
     */
    public function float(mixed $key, mixed $default = 0.0): float
    {
        return $this->allInput()->mustFloat($key, $default);
    }

    /**
     * @inheritDoc
     */
    public function date(mixed $key, mixed $format = null, mixed $tz = null): Carbon
    {
        return $this->allInput()->mustDate($key, null, $format, $tz);
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

        if ($value instanceof Collection) {
            return $value;
        }

        if (\is_array($value)) {
            return collect($value);
        }

        return new Collection([$key => $value]);
    }

    /**
     * Resolve varchar.
     */
    public function varchar(string $key, ?string $default = null): string
    {
        return $this->allInput()->mustString($key, $default);
    }

    /**
     * Resolve nullable string from request.
     */
    public function fastString(string $key, ?string $default = null): ?string
    {
        return $this->allInput()->string($key, $default);
    }

    /**
     * Resolve nullable int from request.
     */
    public function fastInteger(string $key, ?int $default = null): ?int
    {
        return $this->allInput()->int($key, $default);
    }

    /**
     * Resolve nullable float from request.
     */
    public function fastFloat(string $key, ?float $default = null): ?float
    {
        return $this->allInput()->float($key, $default);
    }

    /**
     * Resolve nullable boolean from request.
     */
    public function fastBoolean(string $key, ?bool $default = null): ?bool
    {
        return $this->allInput()->bool($key, $default);
    }

    /**
     * Resolve nullable date from request.
     */
    public function fastDate(string $key, ?Carbon $default = null, ?string $format = null, ?string $tz = null): ?Carbon
    {
        return $this->allInput()->date($key, $default, $format, $tz);
    }

    /**
     * Resolvenullable  file from request.
     */
    public function fastFile(string $key, ?UploadedFile $default = null): ?UploadedFile
    {
        return $this->allInput()->file($key, $default);
    }

    /**
     * Mandatory resolve string from request.
     */
    public function mustFastString(string $key, ?string $default = null): string
    {
        return $this->allInput()->mustString($key, $default);
    }

    /**
     * Mandatory resolve int from request.
     */
    public function mustFastInteger(string $key, ?int $default = null): int
    {
        return $this->allInput()->mustInt($key, $default);
    }

    /**
     * Mandatory resolve float from request.
     */
    public function mustFastFloat(string $key, ?float $default = null): float
    {
        return $this->allInput()->mustFloat($key, $default);
    }

    /**
     * Mandatory resolve boolean from request.
     */
    public function mustFastBoolean(string $key, ?bool $default = null): bool
    {
        return $this->allInput()->mustBool($key, $default);
    }

    /**
     * Mandatory resolve date from request.
     */
    public function mustFastDate(string $key, ?Carbon $default = null, ?string $format = null, ?string $tz = null): Carbon
    {
        return $this->allInput()->mustDate($key, $default, $format, $tz);
    }

    /**
     * Mandatory resolve file from request.
     */
    public function mustFastFile(string $key, ?UploadedFile $default = null): UploadedFile
    {
        return $this->allInput()->mustFile($key, $default);
    }
}

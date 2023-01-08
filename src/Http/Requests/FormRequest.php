<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest as IlluminateFormRequest;
use Illuminate\Routing\Route;
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
     * Route parameters cache.
     */
    protected ?ValidatedInput $routeParameters = null;

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
     * Get route parameters.
     */
    public function routeParameters(): ValidatedInput
    {
        if ($this->routeParameters !== null) {
            return $this->routeParameters;
        }

        $route = $this->route();

        \assert($route instanceof Route);

        return $this->routeParameters = new ValidatedInput($route->parameters());
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
    public function slug(string $key, ?string $default = null): string
    {
        $route = $this->route();

        \assert($route instanceof Route);

        $value = $route->parameter($key, $default);

        \assert(\is_string($value));

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
}

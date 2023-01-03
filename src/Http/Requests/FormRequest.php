<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest as IlluminateFormRequest;
use Illuminate\Routing\Route;
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
    public function slug(string $key, ?string $default = null): ?string
    {
        $route = $this->route();

        \assert($route instanceof Route);

        $value = $route->parameter($key, $default);

        \assert($value === null || \is_string($value));

        return $value;
    }

    /**
     * Mandatory slug getter.
     */
    public function mustSlug(string $key, ?string $default = null): string
    {
        $value = $this->slug($key, $default);

        \assert($value !== null);

        return $value;
    }
}

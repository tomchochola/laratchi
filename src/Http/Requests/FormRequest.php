<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest as IlluminateFormRequest;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Routing\Route;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;
use Tomchochola\Laratchi\Auth\User;
use Tomchochola\Laratchi\Validation\AllInput;
use Tomchochola\Laratchi\Validation\ValidatedInput;
use Tomchochola\Laratchi\Validation\Validity;

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
     * @param array<array-key, array<string, array<string>>> $errors
     */
    public static function createValidationException(?Validator $validator, array $errors): ValidationException
    {
        $validator ??= resolveValidatorFactory()->make([], []);

        foreach ($errors as $field => $exceptions) {
            foreach ($exceptions as $exception => $params) {
                $validator->addFailure((string) $field, $exception, $params);
            }
        }

        return new ValidationException($validator);
    }

    /**
     * Create throttle validation exception.
     *
     * @param array<array-key> $keys
     */
    public static function createThrottleValidationException(?Validator $validator, array $keys, int $seconds, string $rule = 'throttled'): ValidationException
    {
        $validator ??= resolveValidatorFactory()->make([], []);

        foreach ($keys as $key) {
            $validator->addFailure((string) $key, $rule, [
                'seconds' => (string) $seconds,
                'minutes' => (string) \ceil($seconds / 60),
            ]);
        }

        return new ValidationException($validator);
    }

    /**
     * Create unique validation exception.
     *
     * @param array<array-key> $keys
     */
    public static function createUniqueValidationException(?Validator $validator, array $keys): ValidationException
    {
        $validator ??= resolveValidatorFactory()->make([], []);

        return static::createSingleValidationException($validator, $keys, 'Unique');
    }

    /**
     * Create exists validation exception.
     *
     * @param array<array-key> $keys
     */
    public static function createExistsValidationException(?Validator $validator, array $keys): ValidationException
    {
        $validator ??= resolveValidatorFactory()->make([], []);

        return static::createSingleValidationException($validator, $keys, 'Exists');
    }

    /**
     * Create single validation exception.
     *
     * @param array<array-key> $keys
     */
    public static function createSingleValidationException(?Validator $validator, array $keys, string $rule): ValidationException
    {
        $validator ??= resolveValidatorFactory()->make([], []);

        return static::createValidationException($validator, \array_map(static fn (): array => [$rule => []], \array_flip($keys)));
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
    public function slug(string $key = 'slug'): string
    {
        return $this->routeParameters()->mustString($key);
    }

    /**
     * Throw validation exception.
     *
     * @param array<array-key, array<string, array<string>>> $errors
     */
    public function throwValidationException(array $errors): never
    {
        $validator = $this->getValidatorInstance();

        \assert($validator instanceof Validator);

        throw static::createValidationException($validator, $errors);
    }

    /**
     * Throw throttle validation exception.
     *
     * @param array<array-key> $keys
     */
    public function throwThrottleValidationError(array $keys, int $seconds, string $rule = 'throttled'): never
    {
        $validator = $this->getValidatorInstance();

        \assert($validator instanceof Validator);

        throw static::createThrottleValidationException($validator, $keys, $seconds, $rule);
    }

    /**
     * Throw unique validation exception.
     *
     * @param array<array-key> $keys
     */
    public function throwUniqueValidationException(array $keys): never
    {
        $validator = $this->getValidatorInstance();

        \assert($validator instanceof Validator);

        throw static::createUniqueValidationException($validator, $keys);
    }

    /**
     * Throw exists validation exception.
     *
     * @param array<array-key> $keys
     */
    public function throwExistsValidationException(array $keys): never
    {
        $validator = $this->getValidatorInstance();

        \assert($validator instanceof Validator);

        throw static::createExistsValidationException($validator, $keys);
    }

    /**
     * Throw single validation exception.
     *
     * @param array<array-key> $keys
     */
    public function throwSingleValidationException(array $keys, string $rule): never
    {
        $validator = $this->getValidatorInstance();

        \assert($validator instanceof Validator);

        throw static::createSingleValidationException($validator, $keys, $rule);
    }

    /**
     * @inheritDoc
     */
    public function string(mixed $key, mixed $default = null): Stringable
    {
        $value = $this->allInput()->get($key, $default);

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
     * Nullable integer.
     */
    public function nullableInteger(string $key, ?int $default = null): ?int
    {
        return $this->allInput()->int($key, $default);
    }

    /**
     * @inheritDoc
     */
    public function float(mixed $key, mixed $default = 0.0): float
    {
        return $this->allInput()->mustFloat($key, $default);
    }

    /**
     * Nullable float.
     */
    public function nullableFloat(string $key, ?float $default = null): ?float
    {
        return $this->allInput()->float($key, $default);
    }

    /**
     * @inheritDoc
     */
    public function date(mixed $key, mixed $format = null, mixed $tz = null): Carbon
    {
        return $this->allInput()->mustDate($key, null, $format, $tz);
    }

    /**
     * Nullable date.
     */
    public function nullableDate(string $key, ?Carbon $default = null, ?string $format = null, ?string $tz = null): ?Carbon
    {
        return $this->allInput()->date($key, $default, $format, $tz);
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
            return collect($this->allInput()->only($key));
        }

        if ($key === null) {
            return collect($this->allInput()->all());
        }

        $value = $this->allInput()->get($key);

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
     * Nullable varchar.
     */
    public function nullableVarchar(string $key, ?string $default = null): ?string
    {
        return $this->allInput()->string($key, $default);
    }

    /**
     * Get me.
     */
    public function me(): ?User
    {
        return User::auth();
    }

    /**
     * Authenticate user.
     */
    public function auth(): ?User
    {
        return User::auth();
    }

    /**
     * Mandatory user authentication.
     */
    public function mustAuth(): User
    {
        return User::mustAuth();
    }

    /**
     * Guest user.
     */
    public function guest(): bool
    {
        return User::guest();
    }

    /**
     * Mandatory guest authentication.
     */
    public function mustGuest(): bool
    {
        return User::mustGuest();
    }

    /**
     * Must has valid signature.
     */
    public function mustHasValidSignature(): bool
    {
        if (resolveApp()->runningUnitTests()) {
            return true;
        }

        if (! $this->hasValidSignature()) {
            throw new InvalidSignatureException();
        }

        return true;
    }

    /**
     * Merge rules.
     *
     * @param array<string, mixed> $rules
     * @param ?array<int, string> $sort
     *
     * @return array<string, mixed>
     */
    protected function mergeRules(array $rules, bool $signed = false, bool $cursor = false, bool $page = false, ?int $take = null, bool $filter = false, bool $id = false, bool $slug = false, bool $select = false, bool $count = false, bool $filterId = false, bool $filterSearch = false, ?array $sort = null, bool $filterSlug = false, bool $filterNotId = false): array
    {
        if ($signed) {
            $rules = \array_replace($rules, [
                'signature' => Validity::make()->nullable()->filled()->string(null),
                'expires' => Validity::make()->nullable()->filled()->integer(null, null),
            ]);
        }

        if ($cursor) {
            $rules = \array_replace($rules, [
                'cursor' => Validity::make()->nullable()->filled()->string(null)->cursor(),
            ]);
        }

        if ($page) {
            $rules = \array_replace($rules, [
                'page' => Validity::make()->nullable()->filled()->positive(null, null),
            ]);
        }

        if ($take !== null) {
            $rules = \array_replace($rules, [
                'take' => Validity::make()->nullable()->filled()->positive($take > 0 ? $take : null, null)->missingWith(['count']),
            ]);
        }

        if ($filter) {
            $rules = \array_replace($rules, [
                'filter' => Validity::make()->nullable()->filled()->array(null),
            ]);
        }

        if ($id) {
            $rules = \array_replace($rules, [
                'id' => Validity::make()->nullable()->filled()->id()->missingWith(['slug']),
            ]);
        }

        if ($slug) {
            $rules = \array_replace($rules, [
                'slug' => Validity::make()->nullable()->filled()->slug()->missingWith(['id']),
            ]);
        }

        if ($select) {
            $rules = \array_replace($rules, [
                'select' => Validity::make()->nullable()->filled()->true()->missingWith(['count']),
            ]);
        }

        if ($count) {
            $rules = \array_replace($rules, [
                'count' => Validity::make()->nullable()->filled()->true()->missingWith(['take', 'select', 'sort']),
            ]);
        }

        if ($filterId) {
            $rules = \array_replace($rules, [
                'filter.id' => Validity::make()->nullable()->filled()->collection(null)->missingWith(['filter.slug', 'filter.not_id']),
                'filter.id.*' => Validity::make()->required()->distinct()->id(),
            ]);
        }

        if ($filterNotId) {
            $rules = \array_replace($rules, [
                'filter.not_id' => Validity::make()->nullable()->filled()->collection(null)->missingWith(['filter.slug', 'filter.id']),
                'filter.not_id.*' => Validity::make()->required()->distinct()->id(),
            ]);
        }

        if ($filterSlug) {
            $rules = \array_replace($rules, [
                'filter.slug' => Validity::make()->nullable()->filled()->collection(null)->missingWith(['filter.id', 'filter.not_id']),
                'filter.slug.*' => Validity::make()->required()->distinct()->slug(),
            ]);
        }

        if ($filterSearch) {
            $rules = \array_replace($rules, [
                'filter.search' => Validity::make()->nullable()->filled()->varchar(),
            ]);
        }

        if ($sort !== null) {
            $rules = \array_replace($rules, [
                'sort' => Validity::make()->nullable()->filled()->collection(null)->missingWith(['count']),
                'sort.*' => Validity::make()->required()->distinct()->inString($sort),
            ]);
        }

        return $rules;
    }
}

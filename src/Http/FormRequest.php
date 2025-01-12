<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Http;

use Closure;
use Illuminate\Foundation\Http\FormRequest as IlluminateFormRequest;
use Illuminate\Validation\Factory as ValidatorFactory;
use Symfony\Component\HttpFoundation\Response;
use Tomchochola\Laratchi\Cache\Limit;
use Tomchochola\Laratchi\Cache\Throttler;
use Tomchochola\Laratchi\Exceptions\Thrower;
use Tomchochola\Laratchi\Support\AssertTrait;
use Tomchochola\Laratchi\Support\Parser;
use Tomchochola\Laratchi\Support\ParserTrait;
use Tomchochola\Laratchi\Support\ParseTrait;
use Tomchochola\Laratchi\Support\Resolver;

class FormRequest extends IlluminateFormRequest
{
    use AssertTrait;
    use ParserTrait;
    use ParseTrait;

    /**
     * Mixed getter.
     */
    public function mixed(string|null $key = null): mixed
    {
        if ($key === null) {
            return $this->all();
        }

        return $this->input($key);
    }

    /**
     * Validator factory getter.
     */
    public function validatorFactory(): ValidatorFactory
    {
        return Resolver::resolveValidatorFactory();
    }

    /**
     * Thrower getter.
     */
    public function thrower(): Thrower
    {
        return new Thrower($this->validatorFactory()->make([], []));
    }

    /**
     * Signature getter.
     */
    public function signature(): RequestSignature
    {
        return new RequestSignature($this);
    }

    /**
     * Throttler getter.
     *
     * @param Closure(int): never|Closure(int): Response|null $responseCallback
     */
    public function throttler(string $key, int $maxAttempts, int $decaySeconds, Closure|null $responseCallback = null): Throttler
    {
        return new Throttler(new Limit($this->signature()->defaults()->key($key)->hash(), $maxAttempts, $decaySeconds, $responseCallback));
    }

    /**
     * Validate request data.
     *
     * @param array<mixed> $rules
     */
    public function validate(array $rules): Parser
    {
        return new Parser($this->validatorFactory()->make($this->all(), $rules)->validate());
    }

    /**
     * @inheritDoc
     */
    public function validateResolved(): void {}
}

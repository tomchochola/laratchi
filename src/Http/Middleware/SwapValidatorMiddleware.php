<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Http\Middleware;

use Closure;
use Illuminate\Contracts\Translation\Translator as TranslatorContract;
use Illuminate\Contracts\Validation\Factory as ValidationFactoryContract;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory as ValidationFactory;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Tomchochola\Laratchi\Support\Facades\Facade;
use Tomchochola\Laratchi\Validation\StrictSecureValidator;

class SwapValidatorMiddleware
{
    /**
     * Default secure validator.
     *
     * @var class-string<ValidatorContract>
     */
    public static string $secureValidator = StrictSecureValidator::class;

    /**
     * Extend factory with custom resolver.
     *
     * @param class-string<ValidatorContract> $validator
     */
    public static function extend(ValidationFactoryContract $factory, string $validator): void
    {
        \assert($factory instanceof ValidationFactory);

        $factory->resolver(static function (TranslatorContract $translator, array $data, array $rules, array $messages, array $attributes) use ($validator): ValidatorContract {
            return new $validator($translator, $data, $rules, $messages, $attributes);
        });
    }

    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): SymfonyResponse $next
     * @param ?class-string<ValidatorContract> $validator
     */
    public function handle(Request $request, Closure $next, ?string $validator = null): SymfonyResponse
    {
        Facade::afterResolving('validator', static function (ValidationFactoryContract $factory) use ($validator): void {
            static::extend($factory, $validator ?? static::$secureValidator);
        });

        return $next($request);
    }
}

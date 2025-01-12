<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Routing;

use Closure;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Routing\Controller as IlluminateController;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Tomchochola\Laratchi\Http\Requests\FormRequest;
use Tomchochola\Laratchi\Http\Requests\RequestSignature;
use Tomchochola\Laratchi\Support\ThrottleSupport;

class Controller extends IlluminateController
{
    /**
     * Throttle max attempts.
     */
    public static int $throttle = 5;

    /**
     * Throttle decay in seconds.
     */
    public static int $decay = 600;

    /**
     * Throw simple throttle errors.
     */
    public static bool $simpleThrottle = false;

    /**
     * @inheritDoc
     *
     * @param array<mixed> $parameters
     */
    public function callAction(mixed $method, mixed $parameters): SymfonyResponse
    {
        return parent::callAction($method, $parameters);
    }

    /**
     * Register throttle.
     *
     * @param (Closure(int): never)|null $onError
     *
     * @return array{Closure(): void, Closure(): void}
     */
    protected function throttle(Limit $limit, Closure|null $onError = null): array
    {
        return ThrottleSupport::throttle($limit, $onError);
    }

    /**
     * Register throttle and hit.
     *
     * @param (Closure(int): never)|null $onError
     *
     * @return Closure(): void
     */
    protected function hit(Limit $limit, Closure|null $onError = null): Closure
    {
        return ThrottleSupport::hit($limit, $onError);
    }

    /**
     * Throttle limit.
     */
    protected function limit(RequestSignature|string $signature): Limit
    {
        $signature = $signature instanceof RequestSignature ? $signature : new RequestSignature($signature);

        return Limit::perMinutes(static::$decay, static::$throttle)->by($signature->hash());
    }

    /**
     * Throttle callback.
     *
     * @param array<array-key> $keys
     *
     * @return Closure(int): never
     */
    protected function onThrottle(FormRequest $request, array $keys, string $rule = 'throttled'): Closure
    {
        return static function (int $seconds) use ($request, $keys, $rule): never {
            if (static::$simpleThrottle || \count($keys) === 0) {
                throw new ThrottleRequestsException();
            }

            $request->throwThrottleValidationError($keys, $seconds, $rule);
        };
    }
}

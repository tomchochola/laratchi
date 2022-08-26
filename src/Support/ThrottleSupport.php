<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Support;

use Closure;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Validation\ValidationException;

class ThrottleSupport
{
    /**
     * Register throttle.
     *
     * @param (Closure(int): never)|null $onError
     *
     * @return array{Closure(): void, Closure(): void}
     */
    public static function throttle(Limit $limit, ?Closure $onError = null): array
    {
        $hash = $limit->key;

        \assert(\is_string($hash));

        $key = "throttle:{$hash}";

        $rateLimiter = resolveRateLimiter();

        $failed = $rateLimiter->tooManyAttempts($key, $limit->maxAttempts);

        if ($failed) {
            if ($onError === null) {
                throw new ThrottleRequestsException();
            }

            $onError($rateLimiter->availableIn($key));
        }

        return [
            static function () use ($key, $rateLimiter, $limit): void {
                $rateLimiter->hit($key, $limit->decayMinutes * 60);
            },
            static function () use ($key, $rateLimiter): void {
                $rateLimiter->clear($key);
            },
        ];
    }

    /**
     * Throw throttle validation error.
     *
     * @param array<array-key> $keys
     */
    public static function throwThrottleValidationError(array $keys, int $seconds, string $trans = 'passwords.throttled'): never
    {
        throw ValidationException::withMessages(
            \array_map(static fn (): array => [
                mustTransString($trans, [
                    'seconds' => (string) $seconds,
                    'minutes' => (string) \ceil($seconds / 60),
                ]),
            ], \array_flip($keys)),
        );
    }

    /**
     * Register throttle and hit.
     *
     * @param (Closure(int): never)|null $onError
     *
     * @return Closure(): void
     */
    public static function hit(Limit $limit, ?Closure $onError = null): Closure
    {
        [$hit, $clear] = static::throttle($limit, $onError);

        $hit();

        return $clear;
    }
}

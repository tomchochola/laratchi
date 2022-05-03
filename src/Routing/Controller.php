<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Routing;

use Closure;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Routing\Controller as IlluminateController;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class Controller extends IlluminateController
{
    /**
     * Methods that should be wrapped inside DB::transaction().
     *
     * @var array<int, string>
     */
    protected array $transactions = [];

    /**
     * @inheritDoc
     *
     * @param array<mixed> $parameters
     */
    public function callAction(mixed $method, mixed $parameters): SymfonyResponse
    {
        if (\in_array($method, $this->transactions, true) || \in_array('*', $this->transactions, true)) {
            $response = resolveDatabaseManager()->connection()->transaction(fn (): SymfonyResponse => parent::callAction($method, $parameters));
        } else {
            $response = parent::callAction($method, $parameters);
        }

        \assert($response instanceof SymfonyResponse);

        return $response;
    }

    /**
     * Set methods that should be wrapped inside DB::transaction().
     *
     * @param array<int, string> $methods
     */
    protected function transaction(array $methods = ['*']): void
    {
        $this->transactions = $methods;
    }

    /**
     * Register throttle.
     *
     * @param (Closure(int): never)|null $onError
     *
     * @return array{Closure(): void, Closure(): void}
     */
    protected function throttle(Limit $limit, ?Closure $onError = null): array
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
    protected function throwThrottleValidationError(array $keys, int $seconds, string $trans = 'passwords.throttled'): never
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
    protected function hit(Limit $limit, ?Closure $onError = null): Closure
    {
        [$hit, $clear] = $this->throttle($limit, $onError);

        $hit();

        return $clear;
    }
}

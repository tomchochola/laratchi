<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Routing;

use Closure;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Routing\Controller as IlluminateController;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Tomchochola\Laratchi\Support\ThrottleSupport;

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
        return ThrottleSupport::throttle($limit, $onError);
    }

    /**
     * Throw throttle validation error.
     *
     * @param array<array-key> $keys
     */
    protected function throwThrottleValidationError(array $keys, int $seconds, string $trans = 'validation.throttled'): never
    {
        ThrottleSupport::throwThrottleValidationError($keys, $seconds, $trans);
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
        return ThrottleSupport::hit($limit, $onError);
    }
}

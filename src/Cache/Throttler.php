<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Cache;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Tomchochola\Laratchi\Support\Resolver;

class Throttler
{
    /**
     * Rete limiter.
     */
    public RateLimiter $limiter;

    /**
     * Constructor.
     */
    public function __construct(public Limit $limit)
    {
        $this->limiter = Resolver::resolveRateLimiter();
    }

    /**
     * Hash getter.
     */
    public function hash(): string
    {
        return \sprintf('%s:%s', self::class, assertString($this->limit->key));
    }

    /**
     * Throttle.
     *
     * @param Closure(int): never|null $callback
     *
     * @return $this
     */
    public function throttle(?Closure $callback = null): static
    {
        if ($this->failed()) {
            $this->throw($callback);
        }

        return $this;
    }

    /**
     * Throw exception.
     *
     * @param Closure(int): never|null $callback
     */
    public function throw(?Closure $callback = null): never
    {
        if ($callback !== null) {
            $callback($this->availableIn());
        }

        if (isset($this->limit->responseCallback)) {
            throw new HttpResponseException(($this->limit->responseCallback)($this->availableIn()));
        }

        throw new ThrottleRequestsException();
    }

    /**
     * Throttle failed getter.
     */
    public function failed(): bool
    {
        return $this->limiter->tooManyAttempts($this->hash(), $this->limit->maxAttempts);
    }

    /**
     * Available in getter.
     */
    public function availableIn(): int
    {
        return $this->limiter->availableIn($this->hash());
    }

    /**
     * Hit throttle.
     *
     * @return $this
     */
    public function hit(): static
    {
        $this->limiter->hit($this->hash(), $this->limit->decayMinutes * 60);

        return $this;
    }

    /**
     * Clear throttle.
     *
     * @return $this
     */
    public function clear(): static
    {
        $this->limiter->clear($this->hash());

        return $this;
    }
}

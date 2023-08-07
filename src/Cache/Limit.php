<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Cache;

use Closure;
use Illuminate\Cache\RateLimiting\Limit as IlluminateLimit;
use Symfony\Component\HttpFoundation\Response;

class Limit extends IlluminateLimit
{
    /**
     * @inheritDoc
     *
     * @param Closure(int): Response|Closure(int): never|null $responseCallback
     */
    public function __construct(string $key, int $maxAttempts, int $decayMinutes, ?Closure $responseCallback = null)
    {
        parent::__construct($key, $maxAttempts, $decayMinutes);

        $this->responseCallback = $responseCallback;
    }
}

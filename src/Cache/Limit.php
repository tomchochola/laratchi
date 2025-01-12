<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Cache;

use Closure;
use Illuminate\Cache\RateLimiting\Limit as IlluminateLimit;
use Symfony\Component\HttpFoundation\Response;
use Tomchochola\Laratchi\Http\RequestSignature;

class Limit extends IlluminateLimit
{
    /**
     * @inheritDoc
     *
     * @param Closure(): never|Closure(): Response|Closure(int): never|Closure(int): Response|null $responseCallback
     */
    public function __construct(string $key, int $maxAttempts, int $decaySeconds, Closure|null $responseCallback = null)
    {
        parent::__construct($key, $maxAttempts, $decaySeconds);

        $this->responseCallback = $responseCallback;
    }

    /**
     * Default constructor.
     *
     * @param Closure(): never|Closure(): Response|Closure(int): never|Closure(int): Response|null $responseCallback
     */
    public static function default(string $key = '', int $maxAttempts = 3, int $decaySeconds = 3600, Closure|null $responseCallback = null): self
    {
        return new self(RequestSignature::default($key)->hash(), $maxAttempts, $decaySeconds, $responseCallback);
    }
}

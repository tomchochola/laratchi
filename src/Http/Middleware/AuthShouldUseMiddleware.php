<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AuthShouldUseMiddleware
{
    /**
     * Header name.
     */
    final public const HEADER_NAME = 'X-Auth-Guard';

    /**
     * HTTP Exception message.
     */
    final public const ERROR_MESSAGE = 'X-Auth-Guard Header Invalid';

    /**
     * HTTP Exception status.
     */
    final public const ERROR_STATUS = SymfonyResponse::HTTP_BAD_REQUEST;

    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): SymfonyResponse $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): SymfonyResponse
    {
        $want = $request->headers->get(static::HEADER_NAME);

        if ($want === null && \count($guards) === 0) {
            return $next($request);
        }

        $current = resolveAuthManager()->getDefaultDriver();
        $allowed = \array_keys(mustConfigArray('auth.guards'));

        if ($want === null && ! \in_array($current, $guards, true)) {
            $want = $guards[0];
        }

        if (! \in_array($want, $allowed, true)) {
            throw new HttpException(static::ERROR_STATUS, static::ERROR_MESSAGE);
        }

        if (\count($guards) > 0 && ! \in_array($want, $guards, true)) {
            throw new HttpException(static::ERROR_STATUS, static::ERROR_MESSAGE);
        }

        if ($current !== $want) {
            resolveAuthManager()->shouldUse($want);
            resolvePasswordBrokerManager()->setDefaultDriver($want);
        }

        return $next($request);
    }
}

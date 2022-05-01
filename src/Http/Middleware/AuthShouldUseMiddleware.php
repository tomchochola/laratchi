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
        \assert(\count($guards) > 0);

        if (! $request->hasHeader(static::HEADER_NAME)) {
            $defaultGuard = resolveAuthManager()->getDefaultDriver();

            if (! \in_array($defaultGuard, $guards, true)) {
                resolveAuthManager()->shouldUse($guards[0]);
            }

            return $next($request);
        }

        $value = $request->header(static::HEADER_NAME);

        if (\is_array($value)) {
            $value = \end($value);
        }

        if (! \in_array($value, $guards, true)) {
            throw new HttpException(static::ERROR_STATUS, static::ERROR_MESSAGE);
        }

        resolveAuthManager()->shouldUse($value);

        return $next($request);
    }
}

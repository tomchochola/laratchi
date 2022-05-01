<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

class MustBeStatefulMiddleware
{
    /**
     * HTTP Exception message.
     */
    final public const ERROR_MESSAGE = 'Request Must Be Stateful';

    /**
     * HTTP Exception status.
     */
    final public const ERROR_STATUS = SymfonyResponse::HTTP_BAD_REQUEST;

    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): SymfonyResponse $next
     */
    public function handle(Request $request, Closure $next): SymfonyResponse
    {
        if (! $request->hasSession()) {
            throw new HttpException(static::ERROR_STATUS, static::ERROR_MESSAGE);
        }

        return $next($request);
    }
}

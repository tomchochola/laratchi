<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ValidateAcceptHeaderMiddleware
{
    /**
     * Header name.
     */
    final public const HEADER_NAME = 'Accept';

    /**
     * HTTP Exception message.
     */
    final public const ERROR_MESSAGE = 'Accept Header Invalid';

    /**
     * HTTP Exception status.
     */
    final public const ERROR_STATUS = SymfonyResponse::HTTP_NOT_ACCEPTABLE;

    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): SymfonyResponse $next
     */
    public function handle(Request $request, Closure $next, string ...$accepts): SymfonyResponse
    {
        \assert(\count($accepts) > 0);

        if (! $request->hasHeader(static::HEADER_NAME)) {
            return $next($request);
        }

        if (! $request->accepts($accepts)) {
            throw new HttpException(static::ERROR_STATUS, static::ERROR_MESSAGE);
        }

        return $next($request);
    }
}

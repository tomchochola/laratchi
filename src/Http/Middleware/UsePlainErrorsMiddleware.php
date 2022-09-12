<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class UsePlainErrorsMiddleware
{
    /**
     * Plain errors are enabled.
     */
    public static bool $enabled = true;

    /**
     * Plain errors are on.
     */
    public static bool $on = false;

    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): SymfonyResponse $next
     */
    public function handle(Request $request, Closure $next): SymfonyResponse
    {
        if (static::$enabled) {
            static::$on = true;
        }

        return $next($request);
    }
}

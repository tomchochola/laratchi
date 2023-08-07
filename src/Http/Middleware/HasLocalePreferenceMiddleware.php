<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Tomchochola\Laratchi\Auth\User;

class HasLocalePreferenceMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): SymfonyResponse $next
     */
    public function handle(Request $request, Closure $next): SymfonyResponse
    {
        $me = User::auth();

        if ($me === null) {
            return $next($request);
        }

        $locale = $me->preferredLocale();

        $app = resolveApp();

        if ($app->getLocale() !== $locale) {
            $app->setLocale($locale);
        }

        if ($request->getLocale() !== $locale) {
            $request->setLocale($locale);
        }

        return $next($request);
    }
}

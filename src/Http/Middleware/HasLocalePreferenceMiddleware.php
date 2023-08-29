<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Tomchochola\Laratchi\Auth\User;
use Tomchochola\Laratchi\Config\Config;

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

        $config = Config::inject();

        $locale = $me->preferredLocale();

        if ($locale !== $config->appLocale()) {
            $config->setAppLocale($locale);
        }

        if ($locale !== $request->getLocale()) {
            $request->setLocale($locale);
        }

        return $next($request);
    }
}

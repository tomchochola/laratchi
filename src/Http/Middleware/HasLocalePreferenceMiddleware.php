<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Http\Middleware;

use Closure;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class HasLocalePreferenceMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): SymfonyResponse $next
     */
    public function handle(Request $request, Closure $next): SymfonyResponse
    {
        $me = resolveUser();

        if ($me instanceof HasLocalePreference) {
            $locale = $me->preferredLocale();

            if ($locale !== null) {
                $app = resolveApp();

                if ($app->getLocale() !== $locale) {
                    $app->setLocale($locale);
                }
            }
        }

        return $next($request);
    }
}

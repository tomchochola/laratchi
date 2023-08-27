<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Tomchochola\Laratchi\Config\Config;

class SetPreferredLanguageMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): SymfonyResponse $next
     */
    public function handle(Request $request, Closure $next, string ...$locales): SymfonyResponse
    {
        if (\count($locales) === 0) {
            $locales = Config::inject()->appLocales();
        }

        $locale = $request->getPreferredLanguage($locales);

        if ($locale === null) {
            return $next($request);
        }

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

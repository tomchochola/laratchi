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
        $config = Config::inject();

        if (\count($locales) === 0) {
            $locales = $config->appLocales();
        }

        $locale = $request->getPreferredLanguage($locales);

        if ($locale === null) {
            return $next($request);
        }

        if ($config->appLocale() !== $locale) {
            $config->setAppLocale($locale);
        }

        if ($request->getLocale() !== $locale) {
            $request->setLocale($locale);
        }

        return $next($request);
    }
}

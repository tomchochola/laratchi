<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

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
            foreach (mustConfigArray('app.locales') as $locale) {
                \assert(\is_string($locale));

                $locales[] = $locale;
            }
        }

        $locale = $request->getPreferredLanguage($locales);

        if ($locale === null) {
            return $next($request);
        }

        $app = resolveApp();

        if ($app->getLocale() !== $locale) {
            $app->setLocale($locale);
        }

        return $next($request);
    }
}

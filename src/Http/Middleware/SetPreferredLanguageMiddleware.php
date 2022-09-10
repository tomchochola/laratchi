<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SetPreferredLanguageMiddleware
{
    /**
     * HTTP Exception message.
     */
    final public const ERROR_MESSAGE = 'Not Acceptable Language';

    /**
     * HTTP Exception status.
     */
    final public const ERROR_STATUS = SymfonyResponse::HTTP_NOT_ACCEPTABLE;

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

        \assert(\count($locales) > 0);

        $locale = $request->getPreferredLanguage($locales);

        if ($locale === null && $request->expectsJson()) {
            throw new HttpException(static::ERROR_STATUS, static::ERROR_MESSAGE);
        }

        $app = resolveApp();

        if ($locale !== null && $app->getLocale() !== $locale) {
            $app->setLocale($locale);
        }

        return $next($request);
    }
}

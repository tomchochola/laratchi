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
     * Header name.
     */
    final public const HEADER_NAME = 'Accept-Language';

    /**
     * HTTP Exception message.
     */
    final public const ERROR_MESSAGE = 'Accept-Language Header Invalid';

    /**
     * HTTP Exception status.
     */
    final public const ERROR_STATUS = SymfonyResponse::HTTP_BAD_REQUEST;

    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): SymfonyResponse $next
     */
    public function handle(Request $request, Closure $next, string ...$locales): SymfonyResponse
    {
        $allowed = mustConfigArray('app.locales');

        if (\count($locales) === 0) {
            foreach ($allowed as $locale) {
                \assert(\is_string($locale));

                $locales[] = $locale;
            }
        }

        \assert(\count($locales) > 0);

        $locale = $request->getPreferredLanguage($locales);

        if (! \is_string($locale) || ! \in_array($locale, $allowed, true)) {
            throw new HttpException(static::ERROR_STATUS, static::ERROR_MESSAGE);
        }

        $app = resolveApp();

        if ($app->getLocale() !== $locale) {
            $app->setLocale($locale);
        }

        return $next($request);
    }
}

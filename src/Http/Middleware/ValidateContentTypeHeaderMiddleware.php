<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ValidateContentTypeHeaderMiddleware
{
    /**
     * HTTP Exception status.
     */
    final public const ERROR_STATUS = SymfonyResponse::HTTP_UNSUPPORTED_MEDIA_TYPE;

    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): SymfonyResponse $next
     */
    public function handle(Request $request, Closure $next, string ...$contentTypes): SymfonyResponse
    {
        \assert(\count($contentTypes) > 0);

        if ($request->isMethodSafe()) {
            return $next($request);
        }

        $content = $request->getContent();

        if (blank($content)) {
            return $next($request);
        }

        $contentType = $request->getContentType();

        if (! \in_array($contentType, $contentTypes, true)) {
            throw new HttpException(static::ERROR_STATUS);
        }

        return $next($request);
    }
}

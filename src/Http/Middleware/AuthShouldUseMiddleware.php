<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Tomchochola\Laratchi\Config\Config;

class AuthShouldUseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): SymfonyResponse $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): SymfonyResponse
    {
        $config = Config::inject();

        if (\count($guards) === 0) {
            $guards = $config->authGuards();
        }

        $want = $request->headers->get('X-Auth-Guard');
        $current = $config->authDefaultsGuard();

        if ($want === null && ! \in_array($current, $guards, true)) {
            $want = $guards[0];
        }

        if (! \in_array($want, $guards, true)) {
            throw new BadRequestHttpException('X-Auth-Guard Header Invalid');
        }

        if ($config->authDefaultsGuard() !== $want) {
            $config->setAuthDefaultsGuard($want);
        }

        if ($config->authDefaultsPasswords() !== $want) {
            $config->setAuthDefaultsPasswords($want);
        }

        if ($config->authDefaultsProvider() !== $want) {
            $config->setAuthDefaultsProvider($want);
        }

        return $next($request);
    }
}

<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Services;

use Illuminate\Auth\RequestGuard;
use Illuminate\Auth\SessionGuard;
use Illuminate\Auth\TokenGuard;
use Illuminate\Contracts\Auth\Guard as GuardContract;
use Illuminate\Contracts\Auth\UserProvider as UserProviderContract;
use Tomchochola\Laratchi\Auth\DatabaseTokenGuard;

class AuthService
{
    /**
     * Resolve user provider from guard.
     */
    public function userProvider(GuardContract $guard): UserProviderContract
    {
        \assert($guard instanceof SessionGuard || $guard instanceof RequestGuard || $guard instanceof TokenGuard || $guard instanceof DatabaseTokenGuard);

        return $guard->getProvider();
    }
}

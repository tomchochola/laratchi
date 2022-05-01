<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Actions;

use Illuminate\Auth\SessionGuard;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class SessionLoginAction implements LoginActionInterface
{
    /**
     * @inheritDoc
     */
    public function handle(string $guardName, AuthenticatableContract $user, bool $remember): void
    {
        $guard = resolveAuthManager()->guard($guardName);

        \assert($guard instanceof SessionGuard);

        $guard->login($user, $remember);
    }
}

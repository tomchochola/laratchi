<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Actions;

use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Tomchochola\Laratchi\Auth\DatabaseTokenableInterface;
use Tomchochola\Laratchi\Auth\DatabaseTokenGuard;

class DatabaseTokenLoginAction implements LoginActionInterface
{
    /**
     * @inheritDoc
     */
    public function handle(string $guardName, AuthenticatableContract $user, bool $remember): void
    {
        $guard = resolveAuthManager()->guard($guardName);

        \assert($guard instanceof DatabaseTokenGuard);
        \assert($user instanceof DatabaseTokenableInterface);

        $guard->login($user, $remember);
    }
}

<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Actions;

use Tomchochola\Laratchi\Auth\DatabaseTokenableInterface;
use Tomchochola\Laratchi\Auth\DatabaseTokenGuard;

class DatabaseTokenReloginAction implements ReloginActionInterface
{
    /**
     * @inheritDoc
     */
    public function handle(string $guardName): void
    {
        $guard = resolveAuthManager()->guard($guardName);
        $user = $guard->user();

        \assert($guard instanceof DatabaseTokenGuard);
        \assert($user instanceof DatabaseTokenableInterface);

        $guard->login($user, false, false);
    }
}

<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Actions;

use Tomchochola\Laratchi\Auth\DatabaseTokenGuard;

class DatabaseTokenLogoutCurrentDeviceAction implements LogoutCurrentDeviceActionInterface
{
    /**
     * @inheritDoc
     */
    public function handle(string $guardName): void
    {
        $guard = resolveAuthManager()->guard($guardName);

        \assert($guard instanceof DatabaseTokenGuard);

        $guard->logoutCurrentDevice();
    }
}

<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Actions;

use Illuminate\Auth\SessionGuard;

class SessionLogoutCurrentDeviceActionAction implements LogoutCurrentDeviceActionInterface
{
    /**
     * @inheritDoc
     */
    public function handle(string $guardName): void
    {
        $guard = resolveAuthManager()->guard($guardName);

        \assert($guard instanceof SessionGuard);

        $guard->logoutCurrentDevice();
    }
}

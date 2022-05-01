<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Actions;

use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Tomchochola\Laratchi\Auth\DatabaseToken;
use Tomchochola\Laratchi\Auth\DatabaseTokenableInterface;

class LogoutOtherDevicesAction
{
    /**
     * Handle logout other devices action.
     */
    public function handle(AuthenticatableContract $user): void
    {
        if ($user instanceof DatabaseTokenableInterface) {
            inject(DatabaseToken::class)->clear($user);
        }
    }
}

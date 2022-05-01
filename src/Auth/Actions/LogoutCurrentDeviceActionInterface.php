<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Actions;

interface LogoutCurrentDeviceActionInterface
{
    /**
     * Handle logout current device action.
     */
    public function handle(string $guardName): void;
}

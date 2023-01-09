<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Actions;

class LogoutCurrentDeviceActionAction implements LogoutCurrentDeviceActionInterface
{
    /**
     * Map guard driver to logout current device action interface implementation.
     *
     * @var array<string, class-string<LogoutCurrentDeviceActionInterface>>
     */
    public static array $drivers = [
        'session' => SessionLogoutCurrentDeviceActionAction::class,
        'database_token' => DatabaseTokenLogoutCurrentDeviceAction::class,
    ];

    /**
     * @inheritDoc
     */
    public function handle(string $guardName): void
    {
        $driver = mustConfigString("auth.guards.{$guardName}.driver");

        inject(static::$drivers[$driver])->handle($guardName);

        $request = resolveRequest();

        if ($request->hasSession()) {
            $sesion = $request->session();
            $sesion->invalidate();
            $sesion->regenerateToken();
        }
    }
}

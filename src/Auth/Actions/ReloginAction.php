<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Actions;

class ReloginAction implements ReloginActionInterface
{
    /**
     * Map guard driver to relogin action interface implementation.
     *
     * @var array<string, class-string<ReloginActionInterface>>
     */
    public static array $drivers = [
        'session' => SessionReloginAction::class,
        'database_token' => DatabaseTokenReloginAction::class,
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
            $request->session()->regenerate();
        }
    }
}

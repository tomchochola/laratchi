<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Actions;

use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class LoginAction implements LoginActionInterface
{
    /**
     * Map guard driver to login action interface implementation.
     *
     * @var array<string, class-string<LoginActionInterface>>
     */
    public static array $drivers = [
        'session' => SessionLoginAction::class,
        'database_token' => DatabaseTokenLoginAction::class,
    ];

    /**
     * @inheritDoc
     */
    public function handle(string $guardName, AuthenticatableContract $user, bool $remember): void
    {
        $driver = mustConfigString("auth.guards.{$guardName}.driver");

        inject(static::$drivers[$driver])->handle($guardName, $user, $remember);

        $request = resolveRequest();

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }
    }
}

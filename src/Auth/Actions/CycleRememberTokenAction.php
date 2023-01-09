<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Actions;

use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Support\Str;

class CycleRememberTokenAction
{
    /**
     * Remember token length.
     */
    public static int $rememberTokenLength = 60;

    /**
     * Cycle remember token.
     */
    public function handle(AuthenticatableContract $user): void
    {
        $user->setRememberToken(Str::random(static::$rememberTokenLength));
    }
}

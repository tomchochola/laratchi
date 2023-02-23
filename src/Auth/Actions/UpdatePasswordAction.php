<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Actions;

use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;

class UpdatePasswordAction
{
    /**
     * Handle update password action.
     */
    public function handle(AuthenticatableContract $user, string $password, bool $timestamps = true): void
    {
        \assert($user instanceof Model);

        $user->fill(['password' => resolveHasher()->make($password)]);

        if (! $timestamps) {
            $oldTimestamps = $user->timestamps;
            $user->timestamps = false;
        }

        $user->save();

        if (! $timestamps) {
            $user->timestamps = $oldTimestamps;
        }
    }
}

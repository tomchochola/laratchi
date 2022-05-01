<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Actions;

use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

interface LoginActionInterface
{
    /**
     * Handle login action.
     */
    public function handle(string $guardName, AuthenticatableContract $user, bool $remember): void;
}

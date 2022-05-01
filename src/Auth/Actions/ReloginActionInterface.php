<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Actions;

interface ReloginActionInterface
{
    /**
     * Handle relogin action.
     */
    public function handle(string $guardName): void;
}

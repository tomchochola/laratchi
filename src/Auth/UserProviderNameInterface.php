<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth;

interface UserProviderNameInterface
{
    /**
     * Get user provider name.
     */
    public function getUserProviderName(): string;
}

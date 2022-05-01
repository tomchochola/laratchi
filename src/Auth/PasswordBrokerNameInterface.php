<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth;

interface PasswordBrokerNameInterface
{
    /**
     * Get password broker name.
     */
    public function getPasswordBrokerName(): string;
}

<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth;

use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

interface DatabaseTokenableInterface extends AuthenticatableContract, UserProviderNameInterface, PasswordBrokerNameInterface
{
    /**
     * Set database token.
     */
    public function setDatabaseToken(?DatabaseToken $databaseToken): void;

    /**
     * Get database token.
     */
    public function getDatabaseToken(): ?DatabaseToken;
}

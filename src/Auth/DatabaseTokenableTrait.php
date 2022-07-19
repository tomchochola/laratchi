<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Model
 */
trait DatabaseTokenableTrait
{
    /**
     * Database token.
     */
    public ?DatabaseToken $databaseToken = null;

    /**
     * Get password broker name.
     */
    public function getPasswordBrokerName(): string
    {
        return $this->getTable();
    }

    /**
     * Get user provider name.
     */
    public function getUserProviderName(): string
    {
        return $this->getTable();
    }

    /**
     * Set database token.
     */
    public function setDatabaseToken(?DatabaseToken $databaseToken): void
    {
        $this->databaseToken = $databaseToken;
    }

    /**
     * Get database token.
     */
    public function getDatabaseToken(): ?DatabaseToken
    {
        return $this->databaseToken;
    }
}

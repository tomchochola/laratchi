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
        $this->setRelation('databaseToken', $databaseToken);
    }

    /**
     * Get database token.
     */
    public function getDatabaseToken(): ?DatabaseToken
    {
        if (! $this->relationLoaded('databaseToken')) {
            return null;
        }

        $databaseToken = $this->getRelation('databaseToken');

        if ($databaseToken === null) {
            return null;
        }

        \assert($databaseToken instanceof DatabaseToken);

        return $databaseToken;
    }
}

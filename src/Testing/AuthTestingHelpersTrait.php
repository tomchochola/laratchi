<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Testing;

use Illuminate\Foundation\Testing\TestCase;
use Tomchochola\Laratchi\Auth\DatabaseTokenableInterface;
use Tomchochola\Laratchi\Auth\DatabaseTokenGuard;

/**
 * @mixin TestCase
 */
trait AuthTestingHelpersTrait
{
    /**
     * Set default auth guard.
     *
     * @return $this
     */
    public function guard(string $guardName): static
    {
        resolveAuthManager()->shouldUse($guardName);

        return $this;
    }

    /**
     * Set default password broker.
     *
     * @return $this
     */
    public function passwordBroker(string $passwordBrokerName): static
    {
        resolvePasswordBrokerManager()->setDefaultDriver($passwordBrokerName);

        return $this;
    }

    /**
     *  Login user using database token in header.
     *
     * @return $this
     */
    public function beViaDatabaseToken(DatabaseTokenableInterface $user, string $guardName): static
    {
        $guard = resolveAuthManager()->guard($guardName);

        \assert($guard instanceof DatabaseTokenGuard);

        $databaseToken = $guard->createToken($user);

        $user->setDatabaseToken($databaseToken);

        $guard->databaseToken = $databaseToken;

        return $this->be($user, $guardName);
    }
}

<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth;

use Illuminate\Support\Str;
use Tomchochola\Laratchi\Database\Model;

class DatabaseToken extends Model
{
    /**
     * Plain text bearer.
     */
    public string $bearer = '';

    /**
     * @inheritDoc
     */
    protected $hidden = [
        'hash',
        'bearer',
    ];

    /**
     * Find database token matching given bearer.
     */
    public function find(string $bearer): ?static
    {
        if (! \str_contains($bearer, '|')) {
            return null;
        }

        [$id, $token] = \explode('|', $bearer, 2);

        $instance = static::query()->find($id);

        if ($instance === null) {
            return null;
        }

        \assert($instance instanceof static);

        if (! \hash_equals($instance->getHash(), \hash('sha256', $token))) {
            return null;
        }

        return $instance;
    }

    /**
     * Create a new database token instance.
     */
    public function create(DatabaseTokenableInterface $user): static
    {
        $token = Str::random(100);
        $hash = \hash('sha256', $token);

        $authId = $user->getAuthIdentifier();

        \assert(\is_scalar($authId));

        $databaseToken = new static();

        $databaseToken->setHash($hash);
        $databaseToken->setProvider($user->getUserProviderName());
        $databaseToken->setAuthId((string) $authId);

        $ok = $databaseToken->save();

        \assert($ok);

        $databaseToken->bearer = $databaseToken->getKey().'|'.$token;

        return $databaseToken;
    }

    /**
     * Clear database tokens for given user.
     */
    public function clear(DatabaseTokenableInterface $user): void
    {
        $this->newQuery()->where('provider', $user->getUserProviderName())->where('auth_id', $user->getAuthIdentifier())->delete();
    }

    /**
     * Get user.
     */
    public function user(): ?DatabaseTokenableInterface
    {
        return once(function (): ?DatabaseTokenableInterface {
            $user = resolveAuthManager()->createUserProvider($this->getProvider())?->retrieveById($this->getAuthId());

            if ($user === null) {
                return null;
            }

            \assert($user instanceof DatabaseTokenableInterface);

            return $user;
        });
    }

    /**
     * Hash getter.
     */
    public function getHash(): string
    {
        return $this->mustString('hash');
    }

    /**
     * Provider getter.
     */
    public function getProvider(): string
    {
        return $this->mustString('provider');
    }

    /**
     * Auth id getter.
     */
    public function getAuthId(): string
    {
        return $this->mustString('auth_id');
    }

    /**
     * Hash setter.
     */
    public function setHash(string $value): void
    {
        $this->setAttribute('hash', $value);
    }

    /**
     * Provider getter.
     */
    public function setProvider(string $value): void
    {
        $this->setAttribute('provider', $value);
    }

    /**
     * Auth id setter.
     */
    public function setAuthId(string $value): void
    {
        $this->setAttribute('auth_id', $value);
    }
}

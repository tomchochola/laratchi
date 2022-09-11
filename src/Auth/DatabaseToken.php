<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth;

use Illuminate\Support\Str;
use Tomchochola\Laratchi\Database\Model;

/**
 * @property string $provider
 * @property string $auth_id
 * @property string $hash
 */
class DatabaseToken extends Model
{
    /**
     * Plain text bearer.
     */
    public string $bearer = '';

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'provider',
        'auth_id',
        'hash',
    ];

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

        if (! \hash_equals($instance->hash, \hash('sha256', $token))) {
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

        $databaseToken->hash = $hash;
        $databaseToken->provider = $user->getUserProviderName();
        $databaseToken->auth_id = (string) $authId;

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
        $user = resolveAuthManager()->createUserProvider($this->provider)?->retrieveById($this->auth_id);

        if ($user === null) {
            return null;
        }

        \assert($user instanceof DatabaseTokenableInterface);

        return $user;
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
     * Hash getter.
     */
    public function getHash(): string
    {
        return $this->mustString('hash');
    }
}

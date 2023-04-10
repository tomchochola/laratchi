<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Tomchochola\Laratchi\Database\Model;

class DatabaseToken extends Model
{
    /**
     * Template.
     *
     * @var class-string<self>
     */
    public static string $template = self::class;

    /**
     * Plain text bearer.
     */
    public ?string $bearer = null;

    /**
     * @inheritDoc
     */
    protected $hidden = [
        'hash',
        'bearer',
    ];

    /**
     * Inject.
     */
    public static function inject(): self
    {
        return new static::$template();
    }

    /**
     * Find database token matching given bearer.
     */
    public function resolve(string $bearer): ?static
    {
        if (! \str_contains($bearer, '|')) {
            return null;
        }

        [$id, $token] = \explode('|', $bearer, 2);

        $key = \filter_var($id, \FILTER_VALIDATE_INT);

        if ($key === false) {
            return null;
        }

        $instance = $this->newQuery()->find($id);

        if ($instance === null) {
            return null;
        }

        \assert($instance instanceof static);

        if (! \hash_equals($instance->mustString('hash'), \hash('sha256', $token))) {
            return null;
        }

        return $instance;
    }

    /**
     * Login user.
     */
    public function login(string $guardName, User $user): static
    {
        $token = Str::random(40);
        $hash = \hash('sha256', $token);

        $this->setAttribute('hash', $hash);

        $this->relationship($guardName, $user)->associate($user);

        $this->save();

        $this->bearer = "{$this->getKey()}|{$token}";

        return $this;
    }

    /**
     * Auth user.
     */
    public function auth(string $guardName): ?User
    {
        $user = $this->relationship($guardName, null)->getResults();

        \assert($user === null || $user instanceof User);

        return $user;
    }

    /**
     * Relationship.
     */
    protected function relationship(string $guardName, ?User $user): BelongsTo
    {
        $instance = new (mustConfigString("auth.providers.{$guardName}.model"))();

        \assert($instance instanceof User);

        return $this->belongsTo($instance::class, $instance->getForeignKey(), $instance->getKeyName(), 'auth');
    }
}

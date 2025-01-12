<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Tomchochola\Laratchi\Config\Config;
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
    public string|null $bearer = null;

    /**
     * @inheritDoc
     */
    protected $hidden = ['hash', 'bearer'];

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
    public function findByBearer(string $bearer): static|null
    {
        if (!\str_contains($bearer, '|')) {
            return null;
        }

        [$id, $token] = \explode('|', $bearer, 2);

        $key = \filter_var($id, \FILTER_VALIDATE_INT);

        if ($key === false) {
            return null;
        }

        $instance = static::findByKey($key);

        if ($instance === null) {
            return null;
        }

        if (!\hash_equals($instance->mustString('hash'), \hash('sha256', $token))) {
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
     * Get user.
     */
    public function user(string $guardName): User|null
    {
        return \assertNullableInstance($this->relationship($guardName, null)->getResults(), User::class);
    }

    /**
     * Relationship.
     *
     * @return BelongsTo<User, $this>
     */
    protected function relationship(string $guardName, User|null $user): BelongsTo
    {
        $instance = new (Config::inject()->assertA("auth.providers.{$guardName}.model", User::class))();

        return $this->belongsTo($instance::class, $instance->getForeignKey(), $instance->getKeyName(), 'relationship');
    }
}

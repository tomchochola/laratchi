<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth;

use Illuminate\Contracts\Translation\HasLocalePreference as HasLocalePreferenceContract;
use Illuminate\Foundation\Auth\User as IlluminateUser;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Tomchochola\Laratchi\Auth\Notifications\ResetPasswordNotification;
use Tomchochola\Laratchi\Auth\Notifications\VerifyEmailNotification;
use Tomchochola\Laratchi\Database\ModelTrait;

/**
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 * @property ?Carbon $email_verified_at
 * @property string $email
 * @property string $name
 * @property string $password
 * @property ?string $remember_token
 * @property string $locale
 */
class User extends IlluminateUser implements DatabaseTokenableInterface, HasLocalePreferenceContract
{
    use DatabaseTokenableTrait;
    use ModelTrait;
    use Notifiable;

    /**
     * @inheritDoc
     */
    public $preventsLazyLoading = true;

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'locale',
    ];

    /**
     * @inheritDoc
     *
     * @var array<mixed>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'database_token',
    ];

    /**
     * @inheritDoc
     *
     * @var array<mixed>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Resolve user or null.
     *
     * @param array<string|null> $guards
     */
    public static function resolve(array $guards = [null]): ?static
    {
        return resolveUser($guards, static::class);
    }

    /**
     * Resolve user or throw 401.
     *
     * @param array<string|null> $guards
     */
    public static function mustResolve(array $guards = [null]): static
    {
        return mustResolveUser($guards, static::class);
    }

    /**
     * @inheritDoc
     */
    public function sendPasswordResetNotification(mixed $token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * @inheritDoc
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification());
    }

    /**
     * @inheritDoc
     */
    public function preferredLocale(): ?string
    {
        return $this->locale;
    }
}

<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth;

use Illuminate\Contracts\Translation\HasLocalePreference as HasLocalePreferenceContract;
use Illuminate\Foundation\Auth\User as IlluminateUser;
use Illuminate\Notifications\Notifiable;
use Tomchochola\Laratchi\Auth\Notifications\PasswordInitNotification;
use Tomchochola\Laratchi\Auth\Notifications\ResetPasswordNotification;
use Tomchochola\Laratchi\Auth\Notifications\VerifyEmailNotification;
use Tomchochola\Laratchi\Database\ModelTrait;

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
        if (blank($this->getAuthPassword())) {
            $this->notify(new PasswordInitNotification($token));
        } else {
            $this->notify(new ResetPasswordNotification($token));
        }
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
        return $this->mustString('locale');
    }

    /**
     * @inheritDoc
     */
    public function getAuthIdentifier(): int|string
    {
        return $this->getKey();
    }

    /**
     * @inheritDoc
     */
    public function getAuthPassword(): string
    {
        return $this->string('password') ?? '';
    }

    /**
     * @inheritDoc
     */
    public function getRememberToken(): string
    {
        return $this->string($this->getRememberTokenName()) ?? '';
    }

    /**
     * @inheritDoc
     */
    public function getEmailForPasswordReset(): string
    {
        return $this->mustString('email');
    }

    /**
     * @inheritDoc
     */
    public function hasVerifiedEmail(): bool
    {
        return $this->carbon('email_verified_at') !== null;
    }

    /**
     * @inheritDoc
     */
    public function getEmailForVerification(): string
    {
        return $this->mustString('email');
    }
}

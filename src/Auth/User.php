<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Translation\HasLocalePreference as HasLocalePreferenceContract;
use Illuminate\Foundation\Auth\User as IlluminateUser;
use Illuminate\Notifications\Notifiable;
use Tomchochola\Laratchi\Auth\Http\Controllers\EmailVerificationVerifyController;
use Tomchochola\Laratchi\Auth\Notifications\PasswordInitNotification;
use Tomchochola\Laratchi\Auth\Notifications\ResetPasswordNotification;
use Tomchochola\Laratchi\Auth\Notifications\VerifyEmailNotification;
use Tomchochola\Laratchi\Database\IntModelTrait;
use Tomchochola\Laratchi\Database\ModelTrait;
use Tomchochola\Laratchi\Exceptions\MustBeGuestHttpException;

class User extends IlluminateUser implements DatabaseTokenableInterface, HasLocalePreferenceContract
{
    use DatabaseTokenableTrait;
    use IntModelTrait {
        IntModelTrait::getKey insteadof ModelTrait;
        IntModelTrait::findByKey insteadof ModelTrait;
        IntModelTrait::mustFindByKey insteadof ModelTrait;
    }
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
     * @param (Closure(): never)|null $onError
     */
    public static function mustResolve(array $guards = [null], ?Closure $onError = null): static
    {
        return mustResolveUser($guards, static::class, $onError);
    }

    /**
     * User auth or null.
     */
    public static function auth(): ?static
    {
        $me = resolveAuthManager()->guard()->user();

        if ($me === null) {
            return null;
        }

        \assert($me instanceof static);

        return $me;
    }

    /**
     * Mandatory user auth.
     */
    public static function mustAuth(): static
    {
        $me = static::auth();

        if ($me !== null) {
            return $me;
        }

        throw new AuthenticationException();
    }

    /**
     * Guest user.
     */
    public static function guest(): bool
    {
        return resolveAuthManager()->guard()->guest();
    }

    /**
     * Mandatory guest user.
     */
    public static function mustGuest(): bool
    {
        if (static::guest()) {
            return true;
        }

        throw new MustBeGuestHttpException();
    }

    /**
     * @inheritDoc
     */
    public function sendPasswordResetNotification(mixed $token): void
    {
        if ($this->getAuthPassword() === '') {
            $this->notifyPasswordInit($token);
        } else {
            $this->notifyPasswordReset($token);
        }
    }

    /**
     * Send only password reset notification.
     */
    public function notifyPasswordReset(string $token): void
    {
        $this->notify(new ResetPasswordNotification($this->getUserProviderName(), $token));
    }

    /**
     * Send only password init notification.
     */
    public function notifyPasswordInit(string $token): void
    {
        $this->notify(new PasswordInitNotification($this->getUserProviderName(), $token));
    }

    /**
     * @inheritDoc
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification($this->getUserProviderName(), EmailVerificationVerifyController::class));
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
    public function getAuthIdentifier(): int
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

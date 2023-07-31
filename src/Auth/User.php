<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Translation\HasLocalePreference as HasLocalePreferenceContract;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as IlluminateUser;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Tomchochola\Laratchi\Auth\Notifications\EmailVerificationNotification;
use Tomchochola\Laratchi\Auth\Notifications\PasswordInitNotification;
use Tomchochola\Laratchi\Auth\Notifications\PasswordResetNotification;
use Tomchochola\Laratchi\Auth\Services\EmailBrokerService;
use Tomchochola\Laratchi\Database\ModelTrait;
use Tomchochola\Laratchi\Exceptions\MustBeGuestHttpException;
use Tomchochola\Laratchi\Http\JsonApi\JsonApiResource;
use Tomchochola\Laratchi\Http\JsonApi\ModelResource;

class User extends IlluminateUser implements HasLocalePreferenceContract
{
    use ModelTrait;
    use Notifiable;

    /**
     * @inheritDoc
     */
    public $preventsLazyLoading = true;

    /**
     * @inheritDoc
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * @inheritDoc
     *
     * @var array<mixed>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * User auth or null.
     */
    public static function auth(): ?static
    {
        $me = resolveAuthManager()
            ->guard()
            ->user();

        \assert($me === null || $me instanceof static);

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
        return resolveAuthManager()
            ->guard()
            ->guest();
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
        if ($this->getEmailForPasswordReset() === '') {
            return;
        }

        $this->notify(PasswordResetNotification::inject($this->getTable(), $token, $this->getEmailForPasswordReset())->locale($this->preferredLocale()));
    }

    /**
     * Send password init notification.
     */
    public function sendPasswordInitNotification(string $token): void
    {
        if ($this->getEmailForPasswordReset() === '') {
            return;
        }

        $this->notify(PasswordInitNotification::inject($this->getTable(), $token, $this->getEmailForPasswordReset())->locale($this->preferredLocale()));
    }

    /**
     * @inheritDoc
     */
    public function sendEmailVerificationNotification(): void
    {
        if ($this->getEmailForVerification() === '') {
            return;
        }

        $this->notify(
            EmailVerificationNotification::inject(
                $this->getTable(),
                EmailBrokerService::inject()->store($this->getTable(), $this->getEmailForVerification()),
                $this->getEmailForVerification(),
            )->locale($this->preferredLocale()),
        );
    }

    /**
     * @inheritDoc
     */
    public function preferredLocale(): string
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
        return $this->string('email') ?? '';
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
        return $this->string('email') ?? '';
    }

    /**
     * Database token relationship.
     */
    public function databaseTokens(): HasMany
    {
        return $this->hasMany(DatabaseToken::$template);
    }

    /**
     * Me resource.
     */
    public function meResource(): JsonApiResource
    {
        return new ModelResource($this);
    }

    /**
     * @inheritDoc
     */
    protected static function booted(): void
    {
        parent::booted();

        static::updating(static function (self $user): void {
            if ($user->isDirty('password')) {
                if ($user->getRememberToken() !== '') {
                    $user->setRememberToken(Str::random(60));
                }
            }
        });

        static::updated(static function (self $user): void {
            if ($user->wasChanged('password')) {
                $user
                    ->databaseTokens()
                    ->getQuery()
                    ->delete();
            }
        });
    }
}

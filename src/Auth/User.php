<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth;

use Illuminate\Foundation\Auth\User as IlluminateUser;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Tomchochola\Laratchi\Auth\Notifications\ResetPasswordNotification;
use Tomchochola\Laratchi\Auth\Notifications\VerifyEmailNotification;

/**
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 * @property ?Carbon $email_verified_at
 * @property string $email
 * @property string $name
 * @property string $password
 * @property ?string $remember_token
 */
class User extends IlluminateUser implements DatabaseTokenableInterface
{
    use DatabaseTokenableTrait;
    use Notifiable;

    /**
     * @inheritDoc
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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
     * @inheritDoc
     */
    public function getKey(): int
    {
        $value = parent::getKey();

        \assert(\is_int($value));

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function getRouteKey(): string
    {
        $value = parent::getRouteKey();

        \assert(\is_scalar($value));

        return (string) $value;
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
}

<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Services;

use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Str;
use Tomchochola\Laratchi\Auth\Notifications\EmailConfirmationNotification;
use Tomchochola\Laratchi\Config\Config;

class EmailBrokerService
{
    /**
     * Template.
     *
     * @var class-string<self>
     */
    public static string $template = self::class;

    /**
     * Constructor.
     */
    protected function __construct()
    {
    }

    /**
     * Inject.
     */
    public static function inject(): self
    {
        return new static::$template();
    }

    /**
     * Send token.
     */
    public function store(string $guard, string $email): string
    {
        $token = $this->token();

        resolveCache()->set($this->cacheKey($guard, $email), $token, $this->cacheExpiration());

        return $token;
    }

    /**
     * Validate token.
     */
    public function validate(string $guard, string $email, string $token): bool
    {
        if (! Config::inject()->appEnvIs(['production']) && $token === '111111') {
            return true;
        }

        return resolveCache()->get($this->cacheKey($guard, $email)) === $token;
    }

    /**
     * Confirm email.
     */
    public function confirm(string $guard, string $email): void
    {
        resolveCache()->set($this->cacheKey($guard, $email), true, $this->cacheExpiration());
    }

    /**
     * Email is confirmed.
     */
    public function confirmed(string $guard, string $email): bool
    {
        return resolveCache()->get($this->cacheKey($guard, $email)) === true;
    }

    /**
     * Email is pending.
     */
    public function pending(string $guard, string $email): bool
    {
        return resolveCache()->has($this->cacheKey($guard, $email)) && ! $this->confirmed($guard, $email);
    }

    /**
     * Send anonymous notification.
     */
    public function anonymous(string $guard, string $email, string $locale): void
    {
        (new AnonymousNotifiable())->route('mail', $email)->notify(EmailConfirmationNotification::inject($guard, $this->store($guard, $email), $email)->locale($locale));
    }

    /**
     * Cache key.
     */
    protected function cacheKey(string $guard, string $email): string
    {
        return \sprintf("%s:{$guard}:{$email}", static::class);
    }

    /**
     * Token.
     */
    protected function token(): string
    {
        return Str::random(40);
    }

    /**
     * Token expiration in minutes.
     */
    protected function cacheExpiration(): ?int
    {
        return configInt('auth.verification.expire');
    }
}

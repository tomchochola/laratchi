<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Http\Validation;

use Tomchochola\Laratchi\Validation\Validity;

class AuthValidity
{
    /**
     * Template.
     *
     * @var class-string<self>
     */
    public static string $template = self::class;

    /**
     * Allowed locales.
     *
     * @var ?array<int, string>
     */
    public static array|null $allowedLocales = null;

    /**
     * Constructor.
     */
    protected function __construct() {}

    /**
     * Inject.
     */
    public static function inject(): self
    {
        return new static::$template();
    }

    /**
     * Remember validation rules.
     */
    public function remember(): Validity
    {
        return Validity::make()->boolean();
    }

    /**
     * Name validation rules.
     */
    public function name(): Validity
    {
        return Validity::make()->varchar();
    }

    /**
     * Email validation rules.
     */
    public function email(): Validity
    {
        return Validity::make()
            ->varchar()
            ->email();
    }

    /**
     * Password validation rules.
     */
    public function password(): Validity
    {
        return Validity::make()
            ->string(1024)
            ->password();
    }

    /**
     * Password reset token validation rules.
     */
    public function passwordResetToken(): Validity
    {
        return Validity::make()->varchar();
    }

    /**
     * Email verification token validation rules.
     */
    public function emailVerificationToken(): Validity
    {
        return Validity::make()->varchar();
    }

    /**
     * Locale validation rules.
     */
    public function locale(): Validity
    {
        return Validity::make()->inString(static::$allowedLocales ?? \mustConfigArray('app.locales'));
    }

    /**
     * Id validation rules.
     */
    public function id(): Validity
    {
        return Validity::make()->id();
    }

    /**
     * Slug validation rules.
     */
    public function slug(): Validity
    {
        return Validity::make()->slug();
    }

    /**
     * E-mail verified at validation rules.
     */
    public function emailVerifiedAt(): Validity
    {
        return Validity::make()->dateTime();
    }

    /**
     * Remember token validation rules.
     */
    public function rememberToken(): Validity
    {
        return Validity::make()->varchar(100);
    }

    /**
     * Created at validation rules.
     */
    public function createdAt(): Validity
    {
        return Validity::make()->dateTime();
    }

    /**
     * Updated at validation rules.
     */
    public function updatedAt(): Validity
    {
        return Validity::make()->dateTime();
    }
}

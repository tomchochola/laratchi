<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Http\Validation;

use Tomchochola\Laratchi\Validation\Validity;

class AuthValidity
{
    /**
     * Allowed guards.
     *
     * @var array<int, string>
     */
    public static array $allowedGuards = ['users'];

    /**
     * Allowed locales.
     *
     * @var ?array<int, string>
     */
    public static ?array $allowedLocales = null;

    /**
     * Remember validation rules.
     */
    public function remember(string $guardName): Validity
    {
        return Validity::make()->boolean();
    }

    /**
     * Name validation rules.
     */
    public function name(string $guardName): Validity
    {
        return Validity::make()->varchar(255);
    }

    /**
     * Email validation rules.
     */
    public function email(string $guardName): Validity
    {
        return Validity::make()->varchar(255)->email();
    }

    /**
     * Password validation rules.
     */
    public function password(string $guardName): Validity
    {
        return Validity::make()->string(1024)->password();
    }

    /**
     * Password reset token validation rules.
     */
    public function passwordResetToken(string $guardName): Validity
    {
        return Validity::make()->varchar(255);
    }

    /**
     * Locale validation rules.
     */
    public function locale(string $guardName): Validity
    {
        return Validity::make()->string()->in(static::$allowedLocales ?? mustConfigArray('app.locales'));
    }

    /**
     * Guard name validation rules.
     */
    public function guard(): Validity
    {
        return Validity::make()->string()->in(static::$allowedGuards);
    }

    /**
     * Id validation rules.
     */
    public function id(string $guardName): Validity
    {
        return Validity::make()->positive();
    }

    /**
     * E-mail verified at validation rules.
     */
    public function emailVerifiedAt(string $guardName): Validity
    {
        return Validity::make()->string()->date();
    }

    /**
     * Remember token validation rules.
     */
    public function rememberToken(string $guardName): Validity
    {
        return Validity::make()->string(100);
    }

    /**
     * Created at validation rules.
     */
    public function createdAt(string $guardName): Validity
    {
        return Validity::make()->string()->date();
    }

    /**
     * Updated at validation rules.
     */
    public function updatedAt(string $guardName): Validity
    {
        return Validity::make()->string()->date();
    }
}

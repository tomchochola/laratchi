<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Http\Validation;

use Tomchochola\Laratchi\Auth\Actions\CycleRememberTokenAction;
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
     * Password max length.
     */
    public static int $passwordMaxLength = 1024;

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
        return Validity::make()->varchar();
    }

    /**
     * Email validation rules.
     */
    public function email(string $guardName): Validity
    {
        return Validity::make()->varchar()->email();
    }

    /**
     * Password validation rules.
     */
    public function password(string $guardName): Validity
    {
        return Validity::make()->varchar(static::$passwordMaxLength)->password();
    }

    /**
     * Password reset token validation rules.
     */
    public function passwordResetToken(string $guardName): Validity
    {
        return Validity::make()->varchar();
    }

    /**
     * Locale validation rules.
     */
    public function locale(string $guardName): Validity
    {
        return Validity::make()->varchar()->in(static::$allowedLocales ?? mustConfigArray('app.locales'));
    }

    /**
     * Guard name validation rules.
     */
    public function guard(): Validity
    {
        return Validity::make()->varchar()->in(static::$allowedGuards);
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
        return Validity::make()->varchar()->date();
    }

    /**
     * Remember token validation rules.
     */
    public function rememberToken(string $guardName): Validity
    {
        return Validity::make()->varchar(CycleRememberTokenAction::$rememberTokenLength);
    }

    /**
     * Created at validation rules.
     */
    public function createdAt(string $guardName): Validity
    {
        return Validity::make()->varchar()->date();
    }

    /**
     * Updated at validation rules.
     */
    public function updatedAt(string $guardName): Validity
    {
        return Validity::make()->varchar()->date();
    }
}

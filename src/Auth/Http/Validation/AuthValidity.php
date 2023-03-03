<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Http\Validation;

use Tomchochola\Laratchi\Auth\Actions\CycleRememberTokenAction;
use Tomchochola\Laratchi\Validation\Validity;

class AuthValidity
{
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
        \assert(static::$passwordMaxLength <= 1024, 'hashing algorithm performance issue');

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
        return Validity::make()->char(2)->in(mustConfigArray('app.locales'))->if(static::$allowedLocales !== null)->in(static::$allowedLocales ?? []);
    }

    /**
     * Id validation rules.
     */
    public function id(string $guardName): Validity
    {
        return Validity::make()->id();
    }

    /**
     * Slug validation rules.
     */
    public function slug(string $guardName): Validity
    {
        return Validity::make()->slug();
    }

    /**
     * E-mail verified at validation rules.
     */
    public function emailVerifiedAt(string $guardName): Validity
    {
        return Validity::make()->dateTime();
    }

    /**
     * Remember token validation rules.
     */
    public function rememberToken(string $guardName): Validity
    {
        return Validity::make()->char(CycleRememberTokenAction::$rememberTokenLength);
    }

    /**
     * Created at validation rules.
     */
    public function createdAt(string $guardName): Validity
    {
        return Validity::make()->dateTime();
    }

    /**
     * Updated at validation rules.
     */
    public function updatedAt(string $guardName): Validity
    {
        return Validity::make()->dateTime();
    }
}

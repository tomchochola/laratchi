<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Http\Validation;

use Tomchochola\Laratchi\Validation\GenericValidity;
use Tomchochola\Laratchi\Validation\Validity;

class AuthValidity
{
    /**
     * Remember validation rules.
     */
    public function remember(string $guardName): Validity
    {
        return Validity::make()->boolean();
    }

    /**
     * Terms accepted validation rules.
     */
    public function termsAccepted(string $guardName): Validity
    {
        return Validity::make()->boolean();
    }

    /**
     * Name validation rules.
     */
    public function name(string $guardName): Validity
    {
        return Validity::make()->defaultString();
    }

    /**
     * Email validation rules.
     */
    public function email(string $guardName): Validity
    {
        return Validity::make()->defaultString()->email();
    }

    /**
     * Password validation rules.
     */
    public function password(string $guardName): Validity
    {
        return Validity::make()->string()->max(1024)->defaultPassword();
    }

    /**
     * Password reset token validation rules.
     */
    public function passwordResetToken(string $guardName): Validity
    {
        return Validity::make()->string();
    }

    /**
     * Locale validation rules.
     */
    public function locale(string $guardName): Validity
    {
        return inject(GenericValidity::class)->locale();
    }
}

<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Http\Requests;

use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Tomchochola\Laratchi\Http\Requests\SecureFormRequest;

class MeShowRequest extends SecureFormRequest
{
    /**
     * Get guard name.
     */
    public function guardName(): string
    {
        return resolveAuthManager()->getDefaultDriver();
    }

    /**
     * Retrieve user.
     */
    public function retrieveUser(): ?AuthenticatableContract
    {
        return once(function (): ?AuthenticatableContract {
            return resolveUser([$this->guardName()]);
        });
    }
}

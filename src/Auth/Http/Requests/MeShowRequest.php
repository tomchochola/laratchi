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
     * Resolve me.
     */
    public function resolveMe(): ?AuthenticatableContract
    {
        return once(fn (): ?AuthenticatableContract => resolveUser([$this->guardName()]));
    }
}

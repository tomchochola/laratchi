<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Http\Requests;

use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Tomchochola\Laratchi\Http\Requests\SecureFormRequest;

class LogoutCurrentDeviceRequest extends SecureFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): Response|bool
    {
        $this->resolveMe();

        return true;
    }

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
    public function resolveMe(): AuthenticatableContract
    {
        return once(fn (): AuthenticatableContract => mustResolveUser([$this->guardName()]));
    }
}

<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Actions;

use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class CanLoginAction
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(AuthenticatableContract $user): Response
    {
        return Response::allow();
    }
}

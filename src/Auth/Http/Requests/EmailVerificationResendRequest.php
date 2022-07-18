<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Http\Requests;

use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tomchochola\Laratchi\Http\Requests\SecureFormRequest;

class EmailVerificationResendRequest extends SecureFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): Response|bool
    {
        $this->retrieveUser();

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
     * Retrieve user.
     */
    public function retrieveUser(): AuthenticatableContract&MustVerifyEmailContract
    {
        return once(function (): AuthenticatableContract&MustVerifyEmailContract {
            $user = mustResolveUser([$this->guardName()]);

            if (! $user instanceof MustVerifyEmailContract) {
                throw new HttpException(SymfonyResponse::HTTP_FORBIDDEN);
            }

            return $user;
        });
    }
}

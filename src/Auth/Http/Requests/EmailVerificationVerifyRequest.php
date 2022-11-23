<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Http\Requests;

use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Contracts\Auth\UserProvider as UserProviderContract;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tomchochola\Laratchi\Auth\Services\AuthService;
use Tomchochola\Laratchi\Http\Requests\SignedRequest;

class EmailVerificationVerifyRequest extends SignedRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): Response|bool
    {
        if (! $this->hasValidSignature()) {
            return false;
        }

        $user = $this->retrieveUser();

        $routeHash = $this->route('hash');
        $authEmail = $user->getEmailForVerification();

        \assert(\is_string($routeHash));

        if (! \hash_equals($routeHash, \hash('sha256', $authEmail))) {
            return false;
        }

        return true;
    }

    /**
     * Get guard name.
     */
    public function guardName(): string
    {
        return $this->allInput()->mustString('guard');
    }

    /**
     * Retrieve user.
     */
    public function retrieveUser(): AuthenticatableContract&MustVerifyEmailContract
    {
        return once(function (): AuthenticatableContract&MustVerifyEmailContract {
            $id = $this->route('id');

            \assert(\is_string($id));

            $user = $this->userProvider()->retrieveById($id);

            if (! $user instanceof MustVerifyEmailContract) {
                throw new HttpException(SymfonyResponse::HTTP_FORBIDDEN);
            }

            return $user;
        });
    }

    /**
     * Get user provider.
     */
    protected function userProvider(): UserProviderContract
    {
        return inject(AuthService::class)->userProvider(resolveAuthManager()->guard($this->guardName()));
    }
}

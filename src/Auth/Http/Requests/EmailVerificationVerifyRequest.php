<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Http\Requests;

use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Contracts\Auth\UserProvider as UserProviderContract;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tomchochola\Laratchi\Auth\Http\Validation\AuthValidity;
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

        $routeEmail = $this->route('email');
        $authEmail = $user->getEmailForVerification();

        \assert(\is_string($routeEmail));

        if ($routeEmail !== $authEmail) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        $authValidity = inject(AuthValidity::class);

        return \array_merge(parent::rules(), [
            'guard' => $authValidity->guard()->nullable()->filled(),
        ]);
    }

    /**
     * Get guard name.
     */
    public function guardName(): string
    {
        if ($this->has('guard')) {
            return $this->str('guard')->value();
        }

        return resolveAuthManager()->getDefaultDriver();
    }

    /**
     * Retrieve user.
     */
    public function retrieveUser(): AuthenticatableContract&MustVerifyEmailContract
    {
        return once(function (): AuthenticatableContract&MustVerifyEmailContract {
            $user = $this->retrieveById();

            if ($user === null) {
                throw new HttpException(SymfonyResponse::HTTP_UNAUTHORIZED);
            }

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

    /**
     * Retrieve user by id.
     */
    protected function retrieveById(): ?AuthenticatableContract
    {
        return $this->userProvider()->retrieveById($this->route('id'));
    }
}

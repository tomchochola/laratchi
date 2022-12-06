<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Http\Requests;

use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Contracts\Auth\UserProvider as UserProviderContract;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tomchochola\Laratchi\Auth\Http\Validation\AuthValidity;
use Tomchochola\Laratchi\Auth\Services\AuthService;
use Tomchochola\Laratchi\Http\Requests\SecureFormRequest;

class EmailVerificationResendRequest extends SecureFormRequest
{
    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        $authValidity = inject(AuthValidity::class);

        $guardName = $this->guardName();

        $guest = resolveAuthManager()->guard($guardName)->guest();

        return \array_merge(parent::rules(), [
            'guard' => $authValidity->guard()->nullable()->filled(),
            'email' => $authValidity->email($guardName)->nullable()->filled()->requiredIfRule($guest),
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
            $user = $this->retrieveByCredentials() ?? $this->retrieveByGuard();

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
     * Get credentials.
     *
     * @return array<string, mixed>
     */
    public function credentials(): array
    {
        return $this->validatedInput()->only(['email']);
    }

    /**
     * Get user provider.
     */
    protected function userProvider(): UserProviderContract
    {
        return inject(AuthService::class)->userProvider(resolveAuthManager()->guard($this->guardName()));
    }

    /**
     * Retrieve user by credentials.
     */
    protected function retrieveByCredentials(): ?AuthenticatableContract
    {
        return $this->userProvider()->retrieveByCredentials($this->credentials());
    }

    /**
     * Retrieve user by guard.
     */
    protected function retrieveByGuard(): ?AuthenticatableContract
    {
        return resolveUser([$this->guardName()]);
    }
}

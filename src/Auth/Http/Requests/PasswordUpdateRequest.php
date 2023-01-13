<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Http\Requests;

use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Tomchochola\Laratchi\Auth\Http\Validation\AuthValidity;
use Tomchochola\Laratchi\Http\Requests\SecureFormRequest;

class PasswordUpdateRequest extends SecureFormRequest
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
     * @inheritDoc
     */
    public function rules(): array
    {
        $authValidity = inject(AuthValidity::class);

        $guardName = $this->guardName();

        return [
            'guard' => $authValidity->guard()->nullable()->filled(),
            'password' => $authValidity->password($guardName)->required(),
            'new_password' => $authValidity->password($guardName)->required()->confirmed(),
        ];
    }

    /**
     * Get guard name.
     */
    public function guardName(): string
    {
        if ($this->filled('guard')) {
            $guardName = $this->varchar('guard');

            if (\in_array($guardName, \array_keys(mustConfigArray('auth.guards')), true)) {
                return $guardName;
            }
        }

        return resolveAuthManager()->getDefaultDriver();
    }

    /**
     * Retrieve user.
     */
    public function retrieveUser(): AuthenticatableContract
    {
        return once(function (): AuthenticatableContract {
            return mustResolveUser([$this->guardName()]);
        });
    }

    /**
     * Get password.
     *
     * @return array<string, mixed>
     */
    public function password(): array
    {
        return $this->validatedInput()->only(['password']);
    }

    /**
     * Get new password.
     */
    public function newPassword(): string
    {
        return $this->validatedInput()->mustString('new_password');
    }
}

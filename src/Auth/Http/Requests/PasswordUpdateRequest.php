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
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $authValidity = inject(AuthValidity::class);

        $guardName = resolveAuthManager()->getDefaultDriver();

        return \array_merge(parent::rules(), [
            'password' => $authValidity->password($guardName)->required(),
            'new_password' => $authValidity->password($guardName)->required()->confirmed(),
        ]);
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

<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Http\Requests;

use Illuminate\Auth\Access\Response;
use Tomchochola\Laratchi\Auth\Http\Validation\AuthValidity;
use Tomchochola\Laratchi\Http\Requests\SecureFormRequest;

class RegisterRequest extends SecureFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): Response|bool
    {
        mustBeGuest([$this->guardName()]);

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
            'email' => $authValidity->email($guardName)->required(),
            'password' => $authValidity->password($guardName)->confirmed()->required(),
            'name' => $authValidity->name($guardName)->required(),
            'locale' => $authValidity->locale($guardName)->required(),
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
     * Get credentials.
     *
     * @return array<int, array<string, mixed>>
     */
    public function credentials(): array
    {
        return [$this->validatedInput()->only(['email'])];
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
     * Get data.
     *
     * @return array<string, mixed>
     */
    public function data(): array
    {
        return $this->validatedInput()->except(['password', 'password_confirmation']);
    }
}

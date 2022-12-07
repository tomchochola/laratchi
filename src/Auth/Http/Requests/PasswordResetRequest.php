<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Http\Requests;

use Illuminate\Auth\Access\Response;
use Tomchochola\Laratchi\Auth\Http\Validation\AuthValidity;
use Tomchochola\Laratchi\Http\Requests\SecureFormRequest;

class PasswordResetRequest extends SecureFormRequest
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
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $authValidity = inject(AuthValidity::class);

        $guardName = resolveAuthManager()->getDefaultDriver();

        return \array_merge(parent::rules(), [
            'guard' => $authValidity->guard()->nullable()->filled(),
            'token' => $authValidity->passwordResetToken($guardName)->required(),
            'email' => $authValidity->email($guardName)->required(),
            'password' => $authValidity->password($guardName)->required()->confirmed(),
        ]);
    }

    /**
     * Get credentials.
     *
     * @return array<string, mixed>
     */
    public function credentials(): array
    {
        return $this->validatedInput()->only(['email', 'token', 'password']);
    }

    /**
     * Get guard name.
     */
    public function guardName(): string
    {
        if ($this->filled('guard')) {
            $guardName = $this->str('guard')->value();

            if (\in_array($guardName, \array_keys(mustConfigArray('auth.guards')), true)) {
                return $guardName;
            }
        }

        return resolveAuthManager()->getDefaultDriver();
    }

    /**
     * Get password broker name.
     */
    public function passwordBrokerName(): string
    {
        return resolvePasswordBrokerManager()->getDefaultDriver();
    }
}

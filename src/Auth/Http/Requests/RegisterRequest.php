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
    public function authorize(): bool|Response
    {
        $this->mustGuest();

        return true;
    }

    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        $authValidity = AuthValidity::inject();

        return [
            'email' => $authValidity->email()->required(),
            'password' => $authValidity->password()->required(),
            'name' => $authValidity->name()->required(),
            'locale' => $authValidity->locale()->required(),
        ];
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
     * Get data.
     *
     * @return array<string, mixed>
     */
    public function data(): array
    {
        $password = $this->validatedInput()->string('password');

        return $this->validatedInput()
            ->merge([
                'password' => $password === null ? null : \resolveHasher()->make($password),
                'email_verified_at' => null,
                'remember_token' => null,
            ])
            ->all();
    }
}

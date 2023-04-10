<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Http\Requests;

use Illuminate\Auth\Access\Response;
use Tomchochola\Laratchi\Auth\Http\Controllers\RegisterController;
use Tomchochola\Laratchi\Auth\Http\Validation\AuthValidity;
use Tomchochola\Laratchi\Http\Requests\SecureFormRequest;

class RegisterRequest extends SecureFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): Response|bool
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
            'password' => $authValidity->password()->nullable()->filled()->requiredWith(['token']),
            'name' => $authValidity->name()->nullable()->filled()->requiredWith(['token']),
            'locale' => $authValidity->locale()->required(),
            'token' => $authValidity->emailVerificationToken()->nullable()->filled(),
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
        return $this->validatedInput()->merge([
            'password' => resolveHasher()->make($this->validatedInput()->mustString('password')),
            'email_verified_at' => RegisterController::$emailConfirmation ? resolveDate()->now() : null,
            'remember_token' => null,
        ])->except(['token']);
    }
}

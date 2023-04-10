<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Http\Requests;

use Illuminate\Auth\Access\Response;
use Tomchochola\Laratchi\Auth\Http\Controllers\MeUpdateController;
use Tomchochola\Laratchi\Auth\Http\Validation\AuthValidity;
use Tomchochola\Laratchi\Http\Requests\SecureFormRequest;

class MeUpdateRequest extends SecureFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): Response|bool
    {
        $this->mustAuth();

        return true;
    }

    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        $authValidity = AuthValidity::inject();

        return [
            'email' => $authValidity->email()->nullable()->filled(),
            'name' => $authValidity->name()->nullable()->filled(),
            'locale' => $authValidity->locale()->nullable()->filled(),
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
        return $this->validatedInput()->merge(MeUpdateController::$emailConfirmation && $this->has('email') ? ['email_verified_at' => resolveDate()->now()] : [])->except(['token']);
    }
}

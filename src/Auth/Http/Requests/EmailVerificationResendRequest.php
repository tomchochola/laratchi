<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Http\Requests;

use Illuminate\Auth\Access\Response;
use Tomchochola\Laratchi\Auth\Http\Validation\AuthValidity;
use Tomchochola\Laratchi\Http\Requests\SecureFormRequest;

class EmailVerificationResendRequest extends SecureFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): Response|bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        $authValidity = AuthValidity::inject();

        return [
            'email' => $authValidity
                ->email()
                ->nullable()
                ->filled()
                ->requiredIfRule($this->guest()),
        ];
    }

    /**
     * Get credentials.
     *
     * @return array<string, mixed>
     */
    public function credentials(): array
    {
        return $this->validatedInput()->all();
    }
}

<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Http\Requests;

use Illuminate\Auth\Access\Response;
use Tomchochola\Laratchi\Validation\Validity;

class SignedRequest extends SecureFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): Response|bool
    {
        if (! $this->hasValidSignature()) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        return [
            'signature' => Validity::make()->nullable()->filled()->varchar(1024),
            'expires' => Validity::make()->nullable()->filled()->unsigned(),
        ];
    }
}

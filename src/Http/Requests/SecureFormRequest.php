<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Http\Requests;

use Illuminate\Contracts\Validation\Factory as ValidationFactoryContract;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Tomchochola\Laratchi\Validation\SecureValidator;

class SecureFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    protected function createDefaultValidator(ValidationFactoryContract $factory): ValidatorContract
    {
        return parent::createDefaultValidator(SecureValidator::clone($factory));
    }
}

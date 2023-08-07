<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Http;

use Illuminate\Validation\Factory as ValidatorFactory;
use Tomchochola\Laratchi\Validation\SecureValidationFactory;

class ApiFormRequest extends FormRequest
{
    /**
     * @inheritDoc
     */
    public function validatorFactory(): ValidatorFactory
    {
        return new SecureValidationFactory(parent::validatorFactory());
    }
}

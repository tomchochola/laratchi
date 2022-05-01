<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Http\Requests;

use Illuminate\Contracts\Validation\Factory as ValidationFactoryContract;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Tomchochola\Laratchi\Http\Middleware\SwapValidatorMiddleware;

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
        // Prevent global factory override.
        $clonedFactory = clone $factory;

        SwapValidatorMiddleware::extend($clonedFactory, $this->secureValidator());

        return parent::createDefaultValidator($clonedFactory);
    }

    /**
     * Secure validator class string.
     *
     * @return class-string<ValidatorContract>
     */
    protected function secureValidator(): string
    {
        return SwapValidatorMiddleware::$secureValidator;
    }
}

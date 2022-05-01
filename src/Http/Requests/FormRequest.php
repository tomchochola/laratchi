<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest as IlluminateFormRequest;
use Tomchochola\Laratchi\Validation\ValidatedInput;

class FormRequest extends IlluminateFormRequest
{
    /**
     * Validated input cache.
     */
    protected ?ValidatedInput $validatedInput = null;

    /**
     * All input cache.
     */
    protected ?ValidatedInput $allInput = null;

    /**
     * Get a validated input container for the validated input.
     */
    public function validatedInput(): ValidatedInput
    {
        if ($this->validatedInput !== null) {
            return $this->validatedInput;
        }

        $data = $this->validated();

        \assert(\is_array($data));

        return $this->validatedInput = new ValidatedInput($data);
    }

    /**
     * Get a all input container for the all input.
     */
    public function allInput(): ValidatedInput
    {
        if ($this->allInput !== null) {
            return $this->allInput;
        }

        return $this->allInput = new ValidatedInput($this->all());
    }
}

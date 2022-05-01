<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Validation;

class GenericValidity
{
    /**
     * Signature validation rules.
     */
    public function signature(): Validity
    {
        return Validity::make()->string()->size(64);
    }

    /**
     * Expires validation rules.
     */
    public function expires(): Validity
    {
        return Validity::make()->unsignedBigInt();
    }
}

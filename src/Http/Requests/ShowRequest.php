<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Http\Requests;

use Tomchochola\Laratchi\Validation\Validity;

class ShowRequest extends SecureFormRequest
{
    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        return [
            'id' => Validity::make()->id()->nullable()->filled()->requiredWithout(['slug'])->missingWith(['slug']),
            'slug' => Validity::make()->id()->nullable()->filled()->requiredWithout(['id'])->missingWith(['id']),
        ];
    }
}

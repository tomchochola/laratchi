<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Validation;

use Illuminate\Contracts\Translation\Translator as TranslatorContract;

class StrictSecureValidator extends SecureValidator
{
    /**
     * @inheritDoc
     */
    public function __construct(TranslatorContract $translator, array $data, array $rules, array $messages = [], array $customAttributes = [])
    {
        parent::__construct($translator, $data, $rules, $messages, $customAttributes);

        \assert($this->hasAllTranslations($rules));
    }

    /**
     * Check if has translations for all attributes.
     *
     * @param array<mixed> $rules
     */
    protected function hasAllTranslations(array $rules): bool
    {
        foreach (\array_keys($rules) as $attribute) {
            if ($this->getAttributeFromTranslations($attribute) === '') {
                return false;
            }
        }

        return true;
    }
}

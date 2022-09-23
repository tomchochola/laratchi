<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Translation;

use Illuminate\Translation\FileLoader as IlluminateFileLoader;
use Tomchochola\Laratchi\Validation\Validator;

class FileLoader extends IlluminateFileLoader
{
    /**
     * @inheritDoc
     *
     * @return array<mixed>
     */
    public function load(mixed $locale, mixed $group, mixed $namespace = null): array
    {
        if (Validator::$usePlainErrors && resolveRequest()->getRequestFormat() === 'json') {
            if ($group === 'validation' && $namespace === 'validation') {
                $group = 'validation_api';
            } elseif ($group === 'messages' && $namespace === 'validationRules') {
                $group = 'messages_api';
            } elseif ($group === 'validation' && ($namespace === '*' || $namespace === null)) {
                $group = 'validation_api';
            } elseif ($group === 'auth' && ($namespace === '*' || $namespace === null)) {
                $group = 'auth_api';
            } elseif ($group === 'passwords' && ($namespace === '*' || $namespace === null)) {
                $group = 'passwords_api';
            }
        }

        return parent::load($locale, $group, $namespace);
    }
}

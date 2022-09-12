<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Translation;

use Tomchochola\Laratchi\Http\Middleware\UsePlainErrorsMiddleware;

class FileLoader extends \Illuminate\Translation\FileLoader
{
    /**
     * @inheritDoc
     *
     * @return array<mixed>
     */
    public function load(mixed $locale, mixed $group, mixed $namespace = null): array
    {
        if ($group === 'validation' && $namespace === 'validation' && UsePlainErrorsMiddleware::$enabled) {
            $group = 'validation_api';
        } elseif ($group === 'messages' && $namespace === 'validationRules' && UsePlainErrorsMiddleware::$enabled) {
            $group = 'messages_api';
        } elseif ($group === 'validation' && ($namespace === '*' || $namespace === null) && UsePlainErrorsMiddleware::$enabled) {
            $group = 'validation_api';
        }

        return parent::load($locale, $group, $namespace);
    }
}

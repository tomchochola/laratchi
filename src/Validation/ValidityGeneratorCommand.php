<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Validation;

use Illuminate\Console\GeneratorCommand;

class ValidityGeneratorCommand extends GeneratorCommand
{
    /**
     * @inheritDoc
     */
    protected $name = 'make:validity';

    /**
     * @inheritDoc
     */
    protected $description = 'Create a new custom Validity class';

    /**
     * @inheritDoc
     */
    protected $type = 'Validity';

    /**
     * @inheritDoc
     */
    protected function getStub(): string
    {
        return $this->resolveStubPath(pathJoin(['stubs', 'validity.stub']));
    }

    /**
     * Resolve the fully-qualified path to the stub.
     */
    protected function resolveStubPath(string $stub): string
    {
        $customPath = $this->laravel->basePath($stub);

        return \file_exists($customPath)
            ? $customPath
            : pathJoin([__DIR__, 'validity.stub']);
    }

    /**
     * @inheritDoc
     */
    protected function getDefaultNamespace(mixed $rootNamespace): string
    {
        return parent::getDefaultNamespace($rootNamespace).'\\Http\\Validation';
    }
}

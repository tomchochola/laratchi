<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Console;

use Illuminate\Console\GeneratorCommand;

class MakeEnumCommand extends GeneratorCommand
{
    /**
     * @inheritDoc
     */
    protected $name = 'make:enum';

    /**
     * @inheritDoc
     */
    protected $description = 'Create a new custom Enum class';

    /**
     * @inheritDoc
     */
    protected $type = 'Enum';

    /**
     * @inheritDoc
     */
    protected function getStub(): string
    {
        return $this->resolveStubPath();
    }

    /**
     * Resolve the fully-qualified path to the stub.
     */
    protected function resolveStubPath(): string
    {
        $customPath = $this->laravel->basePath('stubs/enum.stub');

        return \file_exists($customPath) ? $customPath : __DIR__.'/stubs/enum.stub';
    }

    /**
     * @inheritDoc
     */
    protected function getDefaultNamespace(mixed $rootNamespace): string
    {
        return parent::getDefaultNamespace($rootNamespace).'\\Enums';
    }
}

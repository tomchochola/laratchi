<?php

declare(strict_types=1);

namespace Tomchochola\LaravelLibrary\Tests;

use Orchestra\Testbench\TestCase;
use Tomchochola\LaravelLibrary\Support\ServiceProvider;

class OrchestraTestCase extends TestCase
{
    /**
     * @inheritDoc
     */
    protected function getPackageProviders(mixed $app): array
    {
        return [
            ServiceProvider::class,
        ];
    }
}

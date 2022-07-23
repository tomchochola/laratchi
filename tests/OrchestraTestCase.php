<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Tests;

use Orchestra\Testbench\TestCase;
use Tomchochola\Laratchi\Support\ServiceProvider;
use Tomchochola\Laratchi\Testing\TestingHelpersTraits;

class OrchestraTestCase extends TestCase
{
    use TestingHelpersTraits;

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

<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Testing;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Storage;

abstract class TestCase extends BaseTestCase
{
    use TestingHelpersTraits;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    /**
     * Locale data provider.
     *
     * @return array<string, array{string}>
     */
    public function localeDataProvider(): array
    {
        return [
            'en' => [
                'en',
            ],
        ];
    }
}

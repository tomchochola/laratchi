<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Providers;

use Tomchochola\Laratchi\Translation\FileLoader;

class TranslationServiceProvider extends \Illuminate\Translation\TranslationServiceProvider
{
    /**
     * Registered file loader.
     *
     * @var class-string<FileLoader>
     */
    public static string $fileLoader = FileLoader::class;

    /**
     * @inheritDoc
     */
    protected function registerLoader(): void
    {
        $this->app->singleton('translation.loader', static function ($app) {
            return new static::$fileLoader($app['files'], $app['path.lang']);
        });
    }
}

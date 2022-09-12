<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Providers;

use Tomchochola\Laratchi\Translation\FileLoader;

class TranslationServiceProvider extends \Illuminate\Translation\TranslationServiceProvider
{
    /**
     * @inheritDoc
     */
    protected function registerLoader(): void
    {
        $this->app->singleton('translation.loader', static function ($app) {
            return new FileLoader($app['files'], $app['path.lang']);
        });
    }
}

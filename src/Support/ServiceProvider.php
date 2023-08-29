<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Support;

use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;
use Tomchochola\Laratchi\Console\MakeEnumCommand;
use Tomchochola\Laratchi\Console\MakeTchiCommand;
use Tomchochola\Laratchi\Testing\TestMailCommand;
use Tomchochola\Laratchi\Validation\ValidityGeneratorCommand;

class ServiceProvider extends IlluminateServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../../lang', 'laratchi');

        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'laratchi');

        if (!$this->app->runningInConsole()) {
            return;
        }

        $app = \assertInstance($this->app, Application::class);

        $this->publishes(
            [
                __DIR__ . '/../../lang' => $app->langPath('vendor/laratchi'),
            ],
            ['laratchi-lang', 'laratchi', 'lang'],
        );

        $this->publishes(
            [
                __DIR__ . '/../../database/migrations' => $app->databasePath('migrations'),
            ],
            ['laratchi-migrations', 'laratchi', 'migrations'],
        );

        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        $this->commands([ValidityGeneratorCommand::class, TestMailCommand::class, MakeTchiCommand::class, MakeEnumCommand::class]);
    }
}

<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Support;

use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;
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
        $this->loadTranslationsFrom(__DIR__.'/../../lang/exceptions', 'exceptions');

        $this->loadViewsFrom(__DIR__.'/../../resources/exceptions/views', 'exceptions');

        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'laratchi');

        if (! $this->app->runningInConsole()) {
            return;
        }

        $app = $this->app;

        \assert($app instanceof Application);

        $this->publishes([
            __DIR__.'/../../lang/exceptions' => $app->langPath('vendor/exceptions'),
        ], ['laratchi-exceptions-lang', 'laratchi-exceptions', 'lang']);

        $this->publishes([
            __DIR__.'/../../database/migrations' => $app->databasePath('migrations'),
        ], ['laratchi-migrations', 'migrations']);

        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        $this->commands([ValidityGeneratorCommand::class, TestMailCommand::class, MakeTchiCommand::class]);
    }
}

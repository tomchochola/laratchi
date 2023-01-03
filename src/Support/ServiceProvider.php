<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Support;

use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;
use Tomchochola\Laratchi\Validation\ValidityGeneratorCommand;

class ServiceProvider extends IlluminateServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadTranslationsFrom(pathJoin([__DIR__, '..', '..', 'lang', 'exceptions']), 'exceptions');

        $this->loadViewsFrom(pathJoin([__DIR__, '..', '..', 'resources', 'exceptions', 'views']), 'exceptions');

        $this->loadViewsFrom(pathJoin([__DIR__, '..', '..', 'resources', 'views']), 'laratchi');

        if (! $this->app->runningInConsole()) {
            return;
        }

        $app = $this->app;

        \assert($app instanceof Application);

        $this->publishes([
            pathJoin([__DIR__, '..', '..', 'lang', 'exceptions']) => $app->langPath(pathJoin(['vendor', 'exceptions'])),
        ], ['laratchi-exceptions-lang', 'laratchi-exceptions', 'lang']);

        $this->publishes([
            pathJoin([__DIR__, '..', '..', 'database', 'migrations']) => $app->databasePath('migrations'),
        ], ['laratchi-migrations', 'migrations']);

        $this->loadMigrationsFrom(pathJoin([__DIR__, '..', '..', 'database', 'migrations']));

        $this->commands([ValidityGeneratorCommand::class]);
    }
}

<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Providers;

use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Validation\Factory as ValidationFactoryContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Tomchochola\Laratchi\Auth\DatabaseTokenGuard;
use Tomchochola\Laratchi\Validation\Validator;

class LaratchiServiceProvider extends ServiceProvider
{
    /**
     * @inheritDoc
     */
    public function register(): void
    {
        parent::register();

        $this->modelRestrictions();
        $this->registerValidator();
        $this->registerDatabaseTokenGuard();
    }

    /**
     * Unguard models and prevent lazy loading.
     */
    protected function modelRestrictions(): void
    {
        Model::unguard();

        if ($this->app->environment('production') !== true) {
            Model::shouldBeStrict();
        }
    }

    /**
     * Register validator.
     */
    protected function registerValidator(): void
    {
        $this->app->afterResolving('validator', static function (ValidationFactoryContract $factory): void {
            Validator::extend($factory, Validator::class);
        });
    }

    /**
     * Register database token guard.
     */
    protected function registerDatabaseTokenGuard(): void
    {
        $this->app->afterResolving('auth', static function (AuthManager $authManager): void {
            $authManager->extend('database_token', static function (Application $app, string $name): DatabaseTokenGuard {
                return new DatabaseTokenGuard($name);
            });
        });
    }
}

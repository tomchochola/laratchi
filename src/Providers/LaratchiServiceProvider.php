<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Providers;

use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Auth\Guard as GuardContract;
use Illuminate\Contracts\Validation\Factory as ValidationFactoryContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Tomchochola\Laratchi\Auth\DatabaseTokenGuard;
use Tomchochola\Laratchi\Auth\Http\Resources\MeResource;
use Tomchochola\Laratchi\Validation\Validator;

class LaratchiServiceProvider extends ServiceProvider
{
    /**
     * Me resource.
     *
     * @var class-string<MeResource>
     */
    public static string $meResource = MeResource::class;

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
            $authManager->extend('database_token', static function (Application $app, string $name, array $config): GuardContract {
                return new DatabaseTokenGuard($name, $config['provider']);
            });
        });
    }
}

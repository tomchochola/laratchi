<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Support;

use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Auth\Guard as GuardContract;
use Illuminate\Contracts\Validation\Factory as ValidationFactoryContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\LazyLoadingViolationException;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;
use Tomchochola\Laratchi\Auth\DatabaseTokenGuard;
use Tomchochola\Laratchi\Auth\Http\Controllers\EmailVerificationResendController;
use Tomchochola\Laratchi\Auth\Http\Controllers\EmailVerificationVerifyController;
use Tomchochola\Laratchi\Auth\Http\Controllers\LoginController;
use Tomchochola\Laratchi\Auth\Http\Controllers\LogoutCurrentDeviceController;
use Tomchochola\Laratchi\Auth\Http\Controllers\LogoutOtherDevicesController;
use Tomchochola\Laratchi\Auth\Http\Controllers\PasswordForgotController;
use Tomchochola\Laratchi\Auth\Http\Controllers\PasswordResetController;
use Tomchochola\Laratchi\Auth\Http\Controllers\PasswordUpdateController;
use Tomchochola\Laratchi\Auth\Http\Controllers\RegisterController;
use Tomchochola\Laratchi\Http\Middleware\SwapValidatorMiddleware;
use Tomchochola\Laratchi\Support\Facades\Facade;
use Tomchochola\Laratchi\Validation\Validator;
use Tomchochola\Laratchi\Validation\ValidityGeneratorCommand;

class ServiceProvider extends IlluminateServiceProvider
{
    /**
     * Register routes.
     */
    public static function registerRoutes(): void
    {
        resolveRouter()->post('login', inject(LoginController::class)::class)->name('login');
        resolveRouter()->post('register', inject(RegisterController::class)::class)->name('register');
        resolveRouter()->post('password/forgot', inject(PasswordForgotController::class)::class)->name('password.forgot');
        resolveRouter()->post('password/reset', inject(PasswordResetController::class)::class)->name('password.reset');
        resolveRouter()->post('password/update', inject(PasswordUpdateController::class)::class)->name('password.update');
        resolveRouter()->post('email_verification/resend', inject(EmailVerificationResendController::class)::class)->name('email_verification.resend');
        resolveRouter()->post('email_verification/verify/{id}/{hash}', inject(EmailVerificationVerifyController::class)::class)->name('email_verification.verify');
        resolveRouter()->post('logout/current', inject(LogoutCurrentDeviceController::class)::class)->name('logout.current');
        resolveRouter()->post('logout/other', inject(LogoutOtherDevicesController::class)::class)->name('logout.other');
    }

    /**
     * Unguard models and prevent lazy loading.
     */
    public static function modelRestrictions(): void
    {
        Model::unguard();
        Model::preventLazyLoading();
        Model::handleLazyLoadingViolationUsing(static function (object $model, string $relation): never {
            throw new LazyLoadingViolationException($model, $relation);
        });
    }

    /**
     * @inheritDoc
     */
    public function register(): void
    {
        parent::register();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Facade::afterResolving('validator', static function (ValidationFactoryContract $factory): void {
            SwapValidatorMiddleware::extend($factory, Validator::class);
        });

        Facade::afterResolving('auth', static function (AuthManager $authManager): void {
            $authManager->extend('database_token', static function (Application $app, string $name, array $config): GuardContract {
                return new DatabaseTokenGuard($name, $config['provider']);
            });
        });

        $this->loadTranslationsFrom(pathJoin([__DIR__, '..', '..', 'lang']), 'exceptions');

        $this->loadViewsFrom(pathJoin([__DIR__, '..', '..', 'resources', 'exceptions', 'views']), 'exceptions');

        $this->loadViewsFrom(pathJoin([__DIR__, '..', '..', 'resources', 'views']), 'laratchi');

        if (! $this->app->runningInConsole()) {
            return;
        }

        $app = $this->app;

        \assert($app instanceof Application);

        $this->publishes([
            pathJoin([__DIR__, '..', '..', 'lang']) => $app->langPath(pathJoin(['vendor', 'exceptions'])),
        ], ['laratchi-exceptions-lang', 'laratchi-exceptions', 'lang']);

        $this->publishes([
            pathJoin([__DIR__, '..', '..', 'database', 'migrations']) => $app->databasePath('migrations'),
        ], ['laratchi-migrations', 'migrations']);

        $this->loadMigrationsFrom(pathJoin([__DIR__, '..', '..', 'database', 'migrations']));

        $this->commands([ValidityGeneratorCommand::class]);
    }
}

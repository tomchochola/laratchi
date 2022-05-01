<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Support;

use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Auth\Guard as GuardContract;
use Illuminate\Contracts\Validation\Factory as ValidationFactoryContract;
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
        resolveRouter()->post('login', inject(LoginController::class)::class);
        resolveRouter()->post('register', inject(RegisterController::class)::class);
        resolveRouter()->post('password/forgot', inject(PasswordForgotController::class)::class);
        resolveRouter()->post('password/reset', inject(PasswordResetController::class)::class);
        resolveRouter()->post('password/update', inject(PasswordUpdateController::class)::class);
        resolveRouter()->post('email_verification/resend', inject(EmailVerificationResendController::class)::class);
        resolveRouter()->post('email_verification/verify/{id}/{hash}', inject(EmailVerificationVerifyController::class)::class);
        resolveRouter()->post('logout/current', inject(LogoutCurrentDeviceController::class)::class);
        resolveRouter()->post('logout/other', inject(LogoutOtherDevicesController::class)::class);
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

        if (! $this->app->runningInConsole()) {
            return;
        }

        $app = $this->app;

        \assert($app instanceof Application);

        $this->publishes([
            pathJoin([__DIR__, '..', '..', 'lang']) => $app->langPath(pathJoin(['vendor', 'exceptions'])),
        ], ['laratchi-exceptions-lang', 'laratchi-exceptions', 'lang']);

        $this->loadMigrationsFrom(pathJoin([__DIR__, '..', '..', 'database', 'migrations']));

        $this->commands([ValidityGeneratorCommand::class]);
    }
}

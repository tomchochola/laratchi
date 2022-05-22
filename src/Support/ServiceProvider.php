<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Support;

use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Auth\Guard as GuardContract;
use Illuminate\Contracts\Validation\Factory as ValidationFactoryContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\LazyLoadingViolationException;
use Illuminate\Foundation\Application;
use Illuminate\Routing\Controller;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;
use Tomchochola\Laratchi\Auth\DatabaseTokenGuard;
use Tomchochola\Laratchi\Auth\Http\Controllers\EmailVerificationResendController;
use Tomchochola\Laratchi\Auth\Http\Controllers\EmailVerificationVerifyController;
use Tomchochola\Laratchi\Auth\Http\Controllers\LoginController;
use Tomchochola\Laratchi\Auth\Http\Controllers\LogoutCurrentDeviceController;
use Tomchochola\Laratchi\Auth\Http\Controllers\LogoutOtherDevicesController;
use Tomchochola\Laratchi\Auth\Http\Controllers\MeDestroyController;
use Tomchochola\Laratchi\Auth\Http\Controllers\MeShowController;
use Tomchochola\Laratchi\Auth\Http\Controllers\MeUpdateController;
use Tomchochola\Laratchi\Auth\Http\Controllers\PasswordForgotController;
use Tomchochola\Laratchi\Auth\Http\Controllers\PasswordResetController;
use Tomchochola\Laratchi\Auth\Http\Controllers\PasswordUpdateController;
use Tomchochola\Laratchi\Auth\Http\Controllers\RegisterController;
use Tomchochola\Laratchi\Http\Middleware\SetPreferredLanguageMiddleware;
use Tomchochola\Laratchi\Http\Middleware\SwapValidatorMiddleware;
use Tomchochola\Laratchi\Support\Facades\Facade;
use Tomchochola\Laratchi\Validation\Validator;
use Tomchochola\Laratchi\Validation\ValidityGeneratorCommand;

class ServiceProvider extends IlluminateServiceProvider
{
    /**
     * Register routes.
     *
     * @param class-string<Controller> $login
     * @param class-string<Controller> $register
     * @param class-string<Controller> $passwordForgot
     * @param class-string<Controller> $passwordReset
     * @param class-string<Controller> $passwordUpdate
     * @param class-string<Controller> $emailResend
     * @param class-string<Controller> $emailVerify
     * @param class-string<Controller> $logoutCurrent
     * @param class-string<Controller> $logoutOther
     * @param class-string<Controller> $meShow
     * @param class-string<Controller> $meDestroy
     * @param class-string<Controller> $meUpdate
     */
    public static function registerRoutes(string $login = LoginController::class, string $register = RegisterController::class, string $passwordForgot = PasswordForgotController::class, string $passwordReset = PasswordResetController::class, string $passwordUpdate = PasswordUpdateController::class, string $emailResend = EmailVerificationResendController::class, string $emailVerify = EmailVerificationVerifyController::class, string $logoutCurrent = LogoutCurrentDeviceController::class, string $logoutOther = LogoutOtherDevicesController::class, string $meShow = MeShowController::class, string $meDestroy = MeDestroyController::class, string $meUpdate = MeUpdateController::class): void
    {
        resolveRouter()->post('login', $login)->name('login');
        resolveRouter()->post('register', $register)->name('register');
        resolveRouter()->post('password/forgot', $passwordForgot)->name('password.forgot');
        resolveRouter()->post('password/reset', $passwordReset)->name('password.reset');
        resolveRouter()->post('password/update', $passwordUpdate)->name('password.update');
        resolveRouter()->post('email_verification/resend', $emailResend)->name('email_verification.resend');
        resolveRouter()->post('email_verification/verify/{id}/{hash}', $emailVerify)->name('email_verification.verify');
        resolveRouter()->post('logout/current', $logoutCurrent)->name('logout.current');
        resolveRouter()->post('logout/other', $logoutOther)->name('logout.other');
        resolveRouter()->get('me', $meShow)->name('me.show');
        resolveRouter()->post('me/destroy', $meDestroy)->name('me.destroy');
        resolveRouter()->post('me/update', $meUpdate)->name('me.update');
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
        resolveKernel()->prependToMiddlewarePriority(SetPreferredLanguageMiddleware::class);

        Facade::afterResolving('validator', static function (ValidationFactoryContract $factory): void {
            SwapValidatorMiddleware::extend($factory, Validator::class);
        });

        Facade::afterResolving('auth', static function (AuthManager $authManager): void {
            $authManager->extend('database_token', static function (Application $app, string $name, array $config): GuardContract {
                return new DatabaseTokenGuard($name, $config['provider']);
            });
        });

        $this->loadTranslationsFrom(pathJoin([__DIR__, '..', '..', 'lang', 'exceptions']), 'exceptions');

        $this->loadViewsFrom(pathJoin([__DIR__, '..', '..', 'resources', 'exceptions', 'views']), 'exceptions');

        $this->loadViewsFrom(pathJoin([__DIR__, '..', '..', 'resources', 'views']), 'laratchi');

        $this->loadTranslationsFrom(pathJoin([__DIR__, '..', '..', 'lang', 'spatie_validation']), 'validationRules');

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

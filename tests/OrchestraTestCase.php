<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Tests;

use Illuminate\Contracts\Debug\ExceptionHandler as IluminateExceptionHandler;
use Illuminate\Contracts\Http\Kernel as IlluminateKernelContract;
use Orchestra\Testbench\TestCase;
use Tomchochola\Laratchi\Auth\User;
use Tomchochola\Laratchi\Exceptions\Handler;
use Tomchochola\Laratchi\Http\Middleware\SwapValidatorMiddleware;
use Tomchochola\Laratchi\Support\ServiceProvider;
use Tomchochola\Laratchi\Validation\SecureValidator;

class OrchestraTestCase extends TestCase
{
    /**
     * @inheritDoc
     */
    protected function getPackageProviders(mixed $app): array
    {
        return [
            ServiceProvider::class,
        ];
    }

    /**
     * @inheritDoc
     */
    protected function resolveApplicationExceptionHandler(mixed $app): void
    {
        $app->singleton(IluminateExceptionHandler::class, Handler::class);
    }

    /**
     * @inheritDoc
     */
    protected function resolveApplicationHttpKernel(mixed $app): void
    {
        $app->singleton(IlluminateKernelContract::class, Kernel::class);
    }

    /**
     * @inheritDoc
     */
    protected function defineEnvironment(mixed $app): void
    {
        ServiceProvider::modelRestrictions();

        SwapValidatorMiddleware::$secureValidator = SecureValidator::class;

        $config = resolveConfig();

        $config->set('auth.guards.database_token', [
            'driver' => 'database_token',
            'provider' => 'users',
        ]);

        $config->set('auth.providers.users.model', User::class);
    }
}

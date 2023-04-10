<?php

declare(strict_types=1);

if (! \function_exists('mustTransString')) {
    /**
     * Mandatory string translation resolver.
     *
     * @param array<string, string> $replace
     */
    function mustTransString(string $key, array $replace = [], ?string $locale = null, bool $fallback = true): string
    {
        $resolved = resolveTranslator()->get($key, $replace, $locale, $fallback);

        \assert(\is_string($resolved) && $resolved !== $key, "[{$key}] translation is missing");
        \assert(\trim($resolved) !== '', "[{$key}] translation is empty");

        return $resolved;
    }
}

if (! \function_exists('mustTransJsonString')) {
    /**
     * Mandatory string json translation resolver.
     *
     * @param array<string, string> $replace
     */
    function mustTransJsonString(string $message, array $replace = [], ?string $locale = null, bool $fallback = true): string
    {
        $resolved = resolveTranslator()->get($message, $replace, $locale, $fallback);

        \assert(\is_string($resolved), "[{$message}] translation is missing");
        \assert(\trim($resolved) !== '', "[{$message}] translation is empty");

        return $resolved;
    }
}

if (! \function_exists('mustTransArray')) {
    /**
     * Mandatory array translation resolver.
     *
     * @param array<string, string> $replace
     *
     * @return array<mixed>
     */
    function mustTransArray(string $key, array $replace = [], ?string $locale = null, bool $fallback = true): array
    {
        $resolved = resolveTranslator()->get($key, $replace, $locale, $fallback);

        \assert(\is_array($resolved), "[{$key}] translation is not array");

        return $resolved;
    }
}

if (! \function_exists('configBool')) {
    /**
     * Config boolean resolver.
     *
     * @param array<mixed> $in
     */
    function configBool(string $key, ?bool $default = null, array $in = []): ?bool
    {
        $value = resolveConfig()->get($key, $default) ?? $default;

        \assert($value === null || \is_bool($value), "[{$key}] config is not bool or null");
        \assert(\count($in) <= 0 || \in_array($value, $in, true), "[{$key}] config is not in available options");

        return $value;
    }
}

if (! \function_exists('mustConfigBool')) {
    /**
     * Mandatory config boolean resolver.
     *
     * @param array<mixed> $in
     */
    function mustConfigBool(string $key, ?bool $default = null, array $in = []): bool
    {
        $value = configBool($key, $default, $in);

        \assert($value !== null, "[{$key}] config is not bool");

        return $value;
    }
}

if (! \function_exists('configInt')) {
    /**
     * Config int resolver.
     *
     * @param array<mixed> $in
     */
    function configInt(string $key, ?int $default = null, array $in = []): ?int
    {
        $value = resolveConfig()->get($key, $default) ?? $default;

        \assert($value === null || \is_int($value), "[{$key}] config is not int or null");
        \assert(\count($in) <= 0 || \in_array($value, $in, true), "[{$key}] config is not in available options");

        return $value;
    }
}

if (! \function_exists('mustConfigInt')) {
    /**
     * Mandatory config int resolver.
     *
     * @param array<mixed> $in
     */
    function mustConfigInt(string $key, ?int $default = null, array $in = []): int
    {
        $value = configInt($key, $default, $in);

        \assert($value !== null, "[{$key}] config is not int");

        return $value;
    }
}

if (! \function_exists('configFloat')) {
    /**
     * Config float resolver.
     *
     * @param array<mixed> $in
     */
    function configFloat(string $key, ?float $default = null, array $in = []): ?float
    {
        $value = resolveConfig()->get($key, $default) ?? $default;

        \assert($value === null || \is_float($value), "[{$key}] config is not float or null");
        \assert(\count($in) <= 0 || \in_array($value, $in, true), "[{$key}] config is not in available options");

        return $value;
    }
}

if (! \function_exists('mustConfigFloat')) {
    /**
     * Mandatory config float resolver.
     *
     * @param array<mixed> $in
     */
    function mustConfigFloat(string $key, ?float $default = null, array $in = []): float
    {
        $value = configFloat($key, $default, $in);

        \assert($value !== null, "[{$key}] config is not float");

        return $value;
    }
}

if (! \function_exists('configArray')) {
    /**
     * Config array resolver.
     *
     * @param array<mixed> $default
     *
     * @return ?array<mixed>
     */
    function configArray(string $key, ?array $default = null): ?array
    {
        $value = resolveConfig()->get($key, $default) ?? $default;

        \assert($value === null || \is_array($value), "[{$key}] config is not array or null");

        return $value;
    }
}

if (! \function_exists('mustConfigArray')) {
    /**
     * Mandatory config array resolver.
     *
     * @param array<mixed> $default
     *
     * @return array<mixed>
     */
    function mustConfigArray(string $key, ?array $default = null): array
    {
        $value = configArray($key, $default);

        \assert($value !== null, "[{$key}] config is not array");

        return $value;
    }
}

if (! \function_exists('configString')) {
    /**
     * Config string resolver.
     *
     * @param array<mixed> $in
     */
    function configString(string $key, ?string $default = null, array $in = []): ?string
    {
        $value = resolveConfig()->get($key, $default) ?? $default;

        \assert(\count($in) <= 0 || \in_array($value, $in, true), "[{$key}] config is not in available options");
        \assert($value === null || \is_string($value), "[{$key}] config is not string or null");

        return $value;
    }
}

if (! \function_exists('mustConfigString')) {
    /**
     * Mandatory config string resolver.
     *
     * @param array<mixed> $in
     */
    function mustConfigString(string $key, ?string $default = null, array $in = []): string
    {
        $value = configString($key, $default, $in);

        \assert($value !== null, "[{$key}] config is not string");

        return $value;
    }
}

if (! \function_exists('inject')) {
    /**
     * Resolve a service from the container.
     *
     * @template T
     *
     * @param class-string<T> $class
     * @param array<mixed> $parameters
     *
     * @return T
     */
    function inject(string $class, array $parameters = []): mixed
    {
        $resolved = resolveApp()->make($class, $parameters);

        \assert($resolved instanceof $class);

        return $resolved;
    }
}

if (! \function_exists('resolveApp')) {
    /**
     * Resolve app.
     */
    function resolveApp(): Illuminate\Foundation\Application
    {
        return Illuminate\Foundation\Application::getInstance();
    }
}

if (! \function_exists('resolveArtisan')) {
    /**
     * Resolve console kernel.
     */
    function resolveArtisan(): Illuminate\Foundation\Console\Kernel
    {
        $resolved = Illuminate\Support\Facades\Artisan::getFacadeRoot();

        \assert($resolved instanceof Illuminate\Foundation\Console\Kernel);

        return $resolved;
    }
}

if (! \function_exists('resolveKernel')) {
    /**
     * Resolve HTTP kernel.
     */
    function resolveKernel(): Illuminate\Foundation\Http\Kernel
    {
        $resolved = resolveApp()->make(Illuminate\Contracts\Http\Kernel::class);

        \assert($resolved instanceof Illuminate\Foundation\Http\Kernel);

        return $resolved;
    }
}

if (! \function_exists('resolveConsoleKernel')) {
    /**
     * Resolve console kernel.
     */
    function resolveConsoleKernel(): Illuminate\Foundation\Console\Kernel
    {
        return resolveArtisan();
    }
}

if (! \function_exists('resolveHttpKernel')) {
    /**
     * Resolve HTTP kernel.
     */
    function resolveHttpKernel(): Illuminate\Foundation\Http\Kernel
    {
        return resolveKernel();
    }
}

if (! \function_exists('resolveAuthManager')) {
    /**
     * Resolve auth manager.
     */
    function resolveAuthManager(): Illuminate\Auth\AuthManager
    {
        $resolved = Illuminate\Support\Facades\Auth::getFacadeRoot();

        \assert($resolved instanceof Illuminate\Auth\AuthManager);

        return $resolved;
    }
}

if (! \function_exists('resolveGuard')) {
    /**
     * Resolve guard.
     */
    function resolveGuard(?string $name = null): Tomchochola\Laratchi\Auth\DatabaseTokenGuard
    {
        $resolved = resolveAuthManager()->guard($name);

        \assert($resolved instanceof Tomchochola\Laratchi\Auth\DatabaseTokenGuard);

        return $resolved;
    }
}

if (! \function_exists('resolveUserProvider')) {
    /**
     * Resolve user provider.
     */
    function resolveUserProvider(?string $name = null): Illuminate\Auth\EloquentUserProvider
    {
        $resolved = resolveAuthManager()->createUserProvider($name);

        \assert($resolved instanceof Illuminate\Auth\EloquentUserProvider);

        return $resolved;
    }
}

if (! \function_exists('resolveBlade')) {
    /**
     * Resolve blade.
     */
    function resolveBlade(): Illuminate\View\Compilers\BladeCompiler
    {
        $resolved = Illuminate\Support\Facades\Blade::getFacadeRoot();

        \assert($resolved instanceof Illuminate\View\Compilers\BladeCompiler);

        return $resolved;
    }
}

if (! \function_exists('resolveBroadcastManager')) {
    /**
     * Resolve broadcast manager.
     */
    function resolveBroadcastManager(): Illuminate\Broadcasting\BroadcastManager
    {
        $resolved = Illuminate\Support\Facades\Broadcast::getFacadeRoot();

        \assert($resolved instanceof Illuminate\Broadcasting\BroadcastManager);

        return $resolved;
    }
}

if (! \function_exists('resolveBus')) {
    /**
     * Resolve bus.
     */
    function resolveBus(): Illuminate\Contracts\Bus\QueueingDispatcher
    {
        $resolved = Illuminate\Support\Facades\Bus::getFacadeRoot();

        \assert($resolved instanceof Illuminate\Contracts\Bus\QueueingDispatcher);

        return $resolved;
    }
}

if (! \function_exists('resolveCacheManager')) {
    /**
     * Resolve cache manager.
     */
    function resolveCacheManager(): Illuminate\Cache\CacheManager
    {
        $resolved = Illuminate\Support\Facades\Cache::getFacadeRoot();

        \assert($resolved instanceof Illuminate\Cache\CacheManager);

        return $resolved;
    }
}

if (! \function_exists('resolveCache')) {
    /**
     * Resolve cache.
     */
    function resolveCache(?string $name = null): Illuminate\Contracts\Cache\Repository
    {
        return resolveCacheManager()->store($name);
    }
}

if (! \function_exists('resolveConfig')) {
    /**
     * Resolve config.
     */
    function resolveConfig(): Illuminate\Config\Repository
    {
        $resolved = Illuminate\Support\Facades\Config::getFacadeRoot();

        \assert($resolved instanceof Illuminate\Config\Repository);

        return $resolved;
    }
}

if (! \function_exists('resolveCookieJar')) {
    /**
     * Resolve cookie.
     */
    function resolveCookieJar(): Illuminate\Cookie\CookieJar
    {
        $resolved = Illuminate\Support\Facades\Cookie::getFacadeRoot();

        \assert($resolved instanceof Illuminate\Cookie\CookieJar);

        return $resolved;
    }
}

if (! \function_exists('resolveEncrypter')) {
    /**
     * Resolve encrypter.
     */
    function resolveEncrypter(): Illuminate\Encryption\Encrypter
    {
        $resolved = Illuminate\Support\Facades\Crypt::getFacadeRoot();

        \assert($resolved instanceof Illuminate\Encryption\Encrypter);

        return $resolved;
    }
}

if (! \function_exists('resolveDatabaseManager')) {
    /**
     * Resolve database manager.
     */
    function resolveDatabaseManager(): Illuminate\Database\DatabaseManager
    {
        $resolved = Illuminate\Support\Facades\DB::getFacadeRoot();

        \assert($resolved instanceof Illuminate\Database\DatabaseManager);

        return $resolved;
    }
}

if (! \function_exists('resolveDatabase')) {
    /**
     * Resolve database.
     */
    function resolveDatabase(?string $name = null): Illuminate\Database\Connection
    {
        return resolveDatabaseManager()->connection($name);
    }
}

if (! \function_exists('resolveEventDispatcher')) {
    /**
     * Resolve event dispatcher.
     */
    function resolveEventDispatcher(): Illuminate\Contracts\Events\Dispatcher
    {
        $resolved = Illuminate\Support\Facades\Event::getFacadeRoot();

        \assert($resolved instanceof Illuminate\Contracts\Events\Dispatcher);

        return $resolved;
    }
}

if (! \function_exists('resolveFilesystem')) {
    /**
     * Resolve filesystem.
     */
    function resolveFilesystem(): Illuminate\Filesystem\Filesystem
    {
        $resolved = Illuminate\Support\Facades\File::getFacadeRoot();

        \assert($resolved instanceof Illuminate\Filesystem\Filesystem);

        return $resolved;
    }
}

if (! \function_exists('resolveGate')) {
    /**
     * Resolve gate.
     */
    function resolveGate(): Illuminate\Auth\Access\Gate
    {
        $resolved = Illuminate\Support\Facades\Gate::getFacadeRoot();

        \assert($resolved instanceof Illuminate\Auth\Access\Gate);

        return $resolved;
    }
}

if (! \function_exists('resolveHashManager')) {
    /**
     * Resolve hash manager.
     */
    function resolveHashManager(): Illuminate\Hashing\HashManager
    {
        $resolved = Illuminate\Support\Facades\Hash::getFacadeRoot();

        \assert($resolved instanceof Illuminate\Hashing\HashManager);

        return $resolved;
    }
}

if (! \function_exists('resolveHasher')) {
    /**
     * Resolve hasher.
     */
    function resolveHasher(?string $driver = null): Illuminate\Contracts\Hashing\Hasher
    {
        $hasher = resolveHashManager()->driver($driver);

        \assert($hasher instanceof Illuminate\Contracts\Hashing\Hasher);

        return $hasher;
    }
}

if (! \function_exists('resolveHttp')) {
    /**
     * Resolve HTTP client.
     */
    function resolveHttp(): Illuminate\Http\Client\Factory
    {
        $resolved = Illuminate\Support\Facades\Http::getFacadeRoot();

        \assert($resolved instanceof Illuminate\Http\Client\Factory);

        return $resolved;
    }
}

if (! \function_exists('resolveTranslator')) {
    /**
     * Resolve translator.
     */
    function resolveTranslator(): Illuminate\Translation\Translator
    {
        $resolved = Illuminate\Support\Facades\Lang::getFacadeRoot();

        \assert($resolved instanceof Illuminate\Translation\Translator);

        return $resolved;
    }
}

if (! \function_exists('resolveLogger')) {
    /**
     * Resolve logger.
     */
    function resolveLogger(): Illuminate\Log\LogManager
    {
        $resolved = Illuminate\Support\Facades\Log::getFacadeRoot();

        \assert($resolved instanceof Illuminate\Log\LogManager);

        return $resolved;
    }
}

if (! \function_exists('resolveMailManager')) {
    /**
     * Resolve mail manager.
     */
    function resolveMailManager(): Illuminate\Contracts\Mail\Factory
    {
        $resolved = Illuminate\Support\Facades\Mail::getFacadeRoot();

        \assert($resolved instanceof Illuminate\Contracts\Mail\Factory);

        return $resolved;
    }
}

if (! \function_exists('resolveNotificator')) {
    /**
     * Resolve notification manager.
     */
    function resolveNotificator(): Illuminate\Contracts\Notifications\Factory
    {
        $resolved = Illuminate\Support\Facades\Notification::getFacadeRoot();

        \assert($resolved instanceof Illuminate\Contracts\Notifications\Factory);

        return $resolved;
    }
}

if (! \function_exists('resolveParallelTesting')) {
    /**
     * Resolve parallel testing.
     */
    function resolveParallelTesting(): Illuminate\Testing\ParallelTesting
    {
        $resolved = Illuminate\Support\Facades\ParallelTesting::getFacadeRoot();

        \assert($resolved instanceof Illuminate\Testing\ParallelTesting);

        return $resolved;
    }
}

if (! \function_exists('resolvePasswordBrokerManager')) {
    /**
     * Resolve password broker manager.
     */
    function resolvePasswordBrokerManager(): Illuminate\Auth\Passwords\PasswordBrokerManager
    {
        $resolved = Illuminate\Support\Facades\Password::getFacadeRoot();

        \assert($resolved instanceof Illuminate\Auth\Passwords\PasswordBrokerManager);

        return $resolved;
    }
}

if (! \function_exists('resolvePasswordBroker')) {
    /**
     * Resolve password broker.
     */
    function resolvePasswordBroker(?string $name = null): Illuminate\Auth\Passwords\PasswordBroker
    {
        $resolved = resolvePasswordBrokerManager()->broker($name);

        \assert($resolved instanceof Illuminate\Auth\Passwords\PasswordBroker);

        return $resolved;
    }
}

if (! \function_exists('resolvePasswordTokenRepository')) {
    /**
     * Resolve password token repository.
     */
    function resolvePasswordTokenRepository(?string $name = null): Illuminate\Auth\Passwords\DatabaseTokenRepository
    {
        $resolved = resolvePasswordBroker($name)->getRepository();

        \assert($resolved instanceof Illuminate\Auth\Passwords\DatabaseTokenRepository);

        return $resolved;
    }
}

if (! \function_exists('resolveQueueManager')) {
    /**
     * Resolve queue manager.
     */
    function resolveQueueManager(): Illuminate\Queue\QueueManager
    {
        $resolved = Illuminate\Support\Facades\Queue::getFacadeRoot();

        \assert($resolved instanceof Illuminate\Queue\QueueManager);

        return $resolved;
    }
}

if (! \function_exists('resolveRateLimiter')) {
    /**
     * Resolve rate limiter.
     */
    function resolveRateLimiter(): Illuminate\Cache\RateLimiter
    {
        $resolved = Illuminate\Support\Facades\RateLimiter::getFacadeRoot();

        \assert($resolved instanceof Illuminate\Cache\RateLimiter);

        return $resolved;
    }
}

if (! \function_exists('resolveRedirector')) {
    /**
     * Resolve redirector.
     */
    function resolveRedirector(): Illuminate\Routing\Redirector
    {
        $resolved = Illuminate\Support\Facades\Redirect::getFacadeRoot();

        \assert($resolved instanceof Illuminate\Routing\Redirector);

        return $resolved;
    }
}

if (! \function_exists('resolveRedisManager')) {
    /**
     * Resolve redis manager.
     */
    function resolveRedisManager(): Illuminate\Redis\RedisManager
    {
        $resolved = Illuminate\Support\Facades\Redis::getFacadeRoot();

        \assert($resolved instanceof Illuminate\Redis\RedisManager);

        return $resolved;
    }
}

if (! \function_exists('resolveRequest')) {
    /**
     * Resolve request.
     */
    function resolveRequest(): Illuminate\Http\Request
    {
        $resolved = Illuminate\Support\Facades\Request::getFacadeRoot();

        \assert($resolved instanceof Illuminate\Http\Request);

        return $resolved;
    }
}

if (! \function_exists('resolveResponseFactory')) {
    /**
     * Resolve response factory.
     */
    function resolveResponseFactory(): Illuminate\Routing\ResponseFactory
    {
        $resolved = Illuminate\Support\Facades\Response::getFacadeRoot();

        \assert($resolved instanceof Illuminate\Routing\ResponseFactory);

        return $resolved;
    }
}

if (! \function_exists('resolveRouter')) {
    /**
     * Resolve router.
     */
    function resolveRouter(): Illuminate\Routing\Router
    {
        $resolved = Illuminate\Support\Facades\Route::getFacadeRoot();

        \assert($resolved instanceof Illuminate\Routing\Router);

        return $resolved;
    }
}

if (! \function_exists('resolveRouteRegistrar')) {
    /**
     * Resolve route registrar.
     */
    function resolveRouteRegistrar(): Illuminate\Routing\RouteRegistrar
    {
        return new Illuminate\Routing\RouteRegistrar(resolveRouter());
    }
}

if (! \function_exists('resolveSchema')) {
    /**
     * Resolve schema.
     */
    function resolveSchema(): Illuminate\Database\Schema\Builder
    {
        $resolved = Illuminate\Support\Facades\Schema::getFacadeRoot();

        \assert($resolved instanceof Illuminate\Database\Schema\Builder);

        return $resolved;
    }
}

if (! \function_exists('resolveSessionManager')) {
    /**
     * Resolve session manager.
     */
    function resolveSessionManager(): Illuminate\Session\SessionManager
    {
        $resolved = Illuminate\Support\Facades\Session::getFacadeRoot();

        \assert($resolved instanceof Illuminate\Session\SessionManager);

        return $resolved;
    }
}

if (! \function_exists('resolveSession')) {
    /**
     * Resolve session.
     */
    function resolveSession(?string $driver = null): Illuminate\Session\Store
    {
        $resolved = resolveSessionManager()->driver($driver);

        \assert($resolved instanceof Illuminate\Session\Store);

        return $resolved;
    }
}

if (! \function_exists('resolveFilesystemManager')) {
    /**
     * Resolve filesystem manager.
     */
    function resolveFilesystemManager(): Illuminate\Filesystem\FilesystemManager
    {
        $resolved = Illuminate\Support\Facades\Storage::getFacadeRoot();

        \assert($resolved instanceof Illuminate\Filesystem\FilesystemManager);

        return $resolved;
    }
}

if (! \function_exists('resolveUrlFactory')) {
    /**
     * Resolve url factory.
     */
    function resolveUrlFactory(): Illuminate\Routing\UrlGenerator
    {
        $resolved = Illuminate\Support\Facades\URL::getFacadeRoot();

        \assert($resolved instanceof Illuminate\Routing\UrlGenerator);

        return $resolved;
    }
}

if (! \function_exists('resolveValidatorFactory')) {
    /**
     * Resolve validator factory.
     */
    function resolveValidatorFactory(): Illuminate\Validation\Factory
    {
        $resolved = Illuminate\Support\Facades\Validator::getFacadeRoot();

        \assert($resolved instanceof Illuminate\Validation\Factory);

        return $resolved;
    }
}

if (! \function_exists('resolveViewFactory')) {
    /**
     * Resolve view factory.
     */
    function resolveViewFactory(): Illuminate\View\Factory
    {
        $resolved = Illuminate\Support\Facades\View::getFacadeRoot();

        \assert($resolved instanceof Illuminate\View\Factory);

        return $resolved;
    }
}

if (! \function_exists('resolveExceptionHandler')) {
    /**
     * Resolve exception handler.
     */
    function resolveExceptionHandler(): Illuminate\Foundation\Exceptions\Handler
    {
        $resolved = resolveApp()->make(Illuminate\Contracts\Debug\ExceptionHandler::class);

        \assert($resolved instanceof Illuminate\Foundation\Exceptions\Handler);

        return $resolved;
    }
}

if (! \function_exists('resolveDate')) {
    /**
     * Resolve date factory.
     */
    function resolveDate(): Illuminate\Support\DateFactory
    {
        $resolved = Illuminate\Support\Facades\Date::getFacadeRoot();

        \assert($resolved instanceof Illuminate\Support\DateFactory);

        return $resolved;
    }
}

if (! \function_exists('resolveMix')) {
    /**
     * Resolve mix.
     */
    function resolveMix(): Illuminate\Foundation\Mix
    {
        return inject(Illuminate\Foundation\Mix::class);
    }
}

if (! \function_exists('resolveVite')) {
    /**
     * Resolve vite.
     */
    function resolveVite(): Illuminate\Foundation\Vite
    {
        return inject(Illuminate\Foundation\Vite::class);
    }
}

if (! \function_exists('unsafeEnv')) {
    /**
     * Unsafe env resolver.
     */
    function unsafeEnv(string $key, mixed $default = null): mixed
    {
        \assert(! resolveApp()->bound('env'), 'read ENV only in config files');

        return Illuminate\Support\Env::get($key, $default);
    }
}

if (! \function_exists('envString')) {
    /**
     * Env string resolver.
     *
     * @param array<mixed> $in
     */
    function envString(string $key, ?string $default = null, bool $trim = true, array $in = []): ?string
    {
        $value = unsafeEnv($key, $default) ?? $default;

        if ($value === null) {
            return null;
        }

        $value = \filter_var($value);

        \assert($value !== false, "[{$key}] env is not string or null");

        if ($trim) {
            $value = \trim($value);
        }

        if ($value === '') {
            $value = $trim && $default !== null ? \trim($default) : $default;
        }

        if ($value === null || $value === '') {
            return null;
        }

        \assert(\count($in) <= 0 || \in_array($value, $in, true), "[{$key}] env is not in available options");

        return $value;
    }
}

if (! \function_exists('mustEnvString')) {
    /**
     * Mandatory env string resolver.
     *
     * @param array<mixed> $in
     */
    function mustEnvString(string $key, ?string $default = null, bool $trim = true, array $in = []): string
    {
        $value = envString($key, $default, $trim, $in);

        \assert($value !== null, "[{$key}] env is not string");

        return $value;
    }
}

if (! \function_exists('envBool')) {
    /**
     * Env bool resolver.
     *
     * @param array<mixed> $in
     */
    function envBool(string $key, ?bool $default = null, array $in = []): ?bool
    {
        $value = unsafeEnv($key, $default) ?? $default;

        if ($value === null) {
            return null;
        }

        $value = \filter_var($value, \FILTER_VALIDATE_BOOL, \FILTER_NULL_ON_FAILURE);

        \assert($value !== null, "[{$key}] env is not bool or null");
        \assert(\count($in) <= 0 || \in_array($value, $in, true), "[{$key}] env is not in available options");

        return $value;
    }
}

if (! \function_exists('mustEnvBool')) {
    /**
     * Mandatory env bool resolver.
     *
     * @param array<mixed> $in
     */
    function mustEnvBool(string $key, ?bool $default = null, array $in = []): bool
    {
        $value = envBool($key, $default, $in);

        \assert($value !== null, "[{$key}] env is not bool");

        return $value;
    }
}

if (! \function_exists('envInt')) {
    /**
     * Env int resolver.
     *
     * @param array<mixed> $in
     */
    function envInt(string $key, ?int $default = null, array $in = []): ?int
    {
        $value = unsafeEnv($key, $default) ?? $default;

        if ($value === null) {
            return null;
        }

        $value = \filter_var($value, \FILTER_VALIDATE_INT);

        \assert($value !== false, "[{$key}] env is not int or null");
        \assert(\count($in) <= 0 || \in_array($value, $in, true), "[{$key}] env is not in available options");

        return $value;
    }
}

if (! \function_exists('mustEnvInt')) {
    /**
     * Mandatory env int resolver.
     *
     * @param array<mixed> $in
     */
    function mustEnvInt(string $key, ?int $default = null, array $in = []): int
    {
        $value = envInt($key, $default, $in);

        \assert($value !== null, "[{$key}] env is not int");

        return $value;
    }
}

if (! \function_exists('envFloat')) {
    /**
     * Env float resolver.
     *
     * @param array<mixed> $in
     */
    function envFloat(string $key, ?float $default = null, array $in = []): ?float
    {
        $value = unsafeEnv($key, $default) ?? $default;

        if ($value === null) {
            return null;
        }

        $value = \filter_var($value, \FILTER_VALIDATE_FLOAT);

        \assert($value !== false, "[{$key}] env is not float or null");
        \assert(\count($in) <= 0 || \in_array($value, $in, true), "[{$key}] env is not in available options");

        return $value;
    }
}

if (! \function_exists('mustEnvFloat')) {
    /**
     * Mandatory env float resolver.
     *
     * @param array<mixed> $in
     */
    function mustEnvFloat(string $key, ?float $default = null, array $in = []): float
    {
        $value = envFloat($key, $default, $in);

        \assert($value !== null, "[{$key}] env is not float");

        return $value;
    }
}

if (! \function_exists('currentEnv')) {
    /**
     * Get current env.
     *
     * @return "local"|"testing"|"development"|"staging"|"production"
     */
    function currentEnv(): string
    {
        $app = resolveApp();

        $value = $app->bound('env') ? $app->make('env') : mustEnvString('APP_ENV');

        \assert(\in_array($value, ['local', 'testing', 'development', 'staging', 'production'], true));

        return $value;
    }
}

if (! \function_exists('isEnv')) {
    /**
     * Check for current env.
     *
     * @param array<"local"|"testing"|"development"|"staging"|"production"> $envs
     */
    function isEnv(array $envs): bool
    {
        return \in_array(currentEnv(), $envs, true);
    }
}

if (! \function_exists('mapEnv')) {
    /**
     * Map env.
     *
     * @param array{local: mixed, testing: mixed, development: mixed, staging: mixed, production: mixed} $mapping
     */
    function mapEnv(array $mapping): mixed
    {
        return $mapping[currentEnv()];
    }
}

if (! \function_exists('assertNever')) {
    /**
     * Assert never.
     */
    function assertNever(string $message = 'assert never'): never
    {
        throw new LogicException($message);
    }
}

if (! \function_exists('assertNeverIf')) {
    /**
     * Assert never if.
     */
    function assertNeverIf(bool $pass, string $message = 'assert never if'): void
    {
        if ($pass) {
            throw new LogicException($message);
        }
    }
}

if (! \function_exists('assertNeverIfNot')) {
    /**
     * Assert never if not.
     */
    function assertNeverIfNot(bool $pass, string $message = 'assert never if not'): void
    {
        if (! $pass) {
            throw new LogicException($message);
        }
    }
}

if (! \function_exists('assertNeverClosure')) {
    /**
     * Assert never closure.
     *
     * @return Closure(): never
     */
    function assertNeverClosure(string $message = 'assert never closure'): Closure
    {
        return static function () use ($message): never {
            throw new LogicException($message);
        };
    }
}

if (! \function_exists('strPutCsv')) {
    /**
     * Encode array to csv.
     *
     * @param array<mixed> $data
     */
    function strPutCsv(array $data): string
    {
        return \implode(',', \array_map(static function (mixed $value): string {
            if (\is_string($value)) {
                return '"'.\str_replace('"', '""', $value).'"';
            }

            if (\is_bool($value)) {
                return $value ? '1' : '0';
            }

            if ($value === null) {
                return '';
            }

            \assert(\is_scalar($value));

            return (string) $value;
        }, $data));
    }
}

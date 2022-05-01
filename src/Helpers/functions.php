<?php

declare(strict_types=1);

if (! \function_exists('randomElement')) {
    /**
     * Select random element from array or return null on empty array.
     *
     * @template T
     *
     * @param array<T> $arr
     *
     * @return ?T
     */
    function randomElement(array $arr): mixed
    {
        if (\count($arr) === 0) {
            return null;
        }

        return $arr[\array_rand($arr)];
    }
}

if (! \function_exists('mustRandomElement')) {
    /**
     * Select random element from array.
     *
     * @template T
     *
     * @param non-empty-array<T> $arr
     *
     * @return T
     */
    function mustRandomElement(array $arr): mixed
    {
        return $arr[\array_rand($arr)];
    }
}

if (! \function_exists('extendedTrim')) {
    /**
     * Trim string using defaults plus provided characters.
     */
    function extendedTrim(string $string, string $characters = ''): string
    {
        return \trim($string, " \t\n\r\0\x0B{$characters}");
    }
}

if (! \function_exists('arrayFilterNull')) {
    /**
     * Filter null values from array.
     *
     * @param array<mixed> $array
     *
     * @return array<mixed>
     */
    function arrayFilterNull(array $array): array
    {
        return \array_filter($array, static fn (mixed $el): bool => $el !== null);
    }
}

if (! \function_exists('randomFloat')) {
    /**
     * Random float generator.
     */
    function randomFloat(float $min, float $max): float
    {
        return $min + \mt_rand() / \mt_getrandmax() * ($max - $min);
    }
}

if (! \function_exists('nonProductionThrow')) {
    /**
     * Throw exception only on production.
     */
    function nonProductionThrow(Throwable $throwable): void
    {
        if (resolveApp()->environment(['production']) === false) {
            throw $throwable;
        }

        resolveExceptionHandler()->report($throwable);
    }
}

if (! \function_exists('mustTransString')) {
    /**
     * Mandatory string translation resolver.
     *
     * @param array<string, string> $replace
     *
     * @return non-empty-string
     */
    function mustTransString(string $key, array $replace = [], ?string $locale = null, bool $fallback = true, bool $trim = true): string
    {
        $resolved = resolveTranslator()->get($key, $replace, $locale, $fallback);

        \assert(\is_string($resolved) && $resolved !== $key);

        if ($trim) {
            $resolved = \trim($resolved);
        }

        \assert($resolved !== '');

        return $resolved;
    }
}

if (! \function_exists('mustTransJsonString')) {
    /**
     * Mandatory string json translation resolver.
     *
     * @param array<string> $messageLocales
     * @param array<string, string> $replace
     *
     * @return non-empty-string
     */
    function mustTransJsonString(string $message, array $messageLocales = ['en'], array $replace = [], ?string $locale = null, bool $fallback = true, bool $trim = true): string
    {
        $translator = resolveTranslator();

        $resolved = $translator->get($message, $replace, $locale, $fallback);

        \assert(\is_string($resolved) && ($resolved !== $message || \in_array($translator->getLocale(), $messageLocales, true)));

        if ($trim) {
            $resolved = \trim($resolved);
        }

        \assert($resolved !== '');

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

        \assert(\is_array($resolved));

        return $resolved;
    }
}

if (! \function_exists('pathJoin')) {
    /**
     * Join paths using directory separator.
     *
     * @param array<string> $paths
     */
    function pathJoin(array $paths): string
    {
        return \implode(\DIRECTORY_SEPARATOR, $paths);
    }
}

if (! \function_exists('configBool')) {
    /**
     * Config boolean resolver.
     */
    function configBool(string $key, ?bool $default = null): ?bool
    {
        $value = resolveConfig()->get($key, $default);

        \assert($value === null || \is_bool($value));

        return $value;
    }
}

if (! \function_exists('mustConfigBool')) {
    /**
     * Mandatory config boolean resolver.
     */
    function mustConfigBool(string $key, ?bool $default = null): bool
    {
        $value = configBool($key, $default);

        \assert($value !== null);

        return $value;
    }
}

if (! \function_exists('configInt')) {
    /**
     * Config int resolver.
     */
    function configInt(string $key, ?int $default = null): ?int
    {
        $value = resolveConfig()->get($key, $default);

        \assert($value === null || \is_int($value));

        return $value;
    }
}

if (! \function_exists('mustConfigInt')) {
    /**
     * Mandatory config int resolver.
     */
    function mustConfigInt(string $key, ?int $default = null): int
    {
        $value = configInt($key, $default);

        \assert($value !== null);

        return $value;
    }
}

if (! \function_exists('configFloat')) {
    /**
     * Config float resolver.
     */
    function configFloat(string $key, ?float $default = null): ?float
    {
        $value = resolveConfig()->get($key, $default);

        \assert($value === null || \is_float($value));

        return $value;
    }
}

if (! \function_exists('mustConfigFloat')) {
    /**
     * Mandatory config float resolver.
     */
    function mustConfigFloat(string $key, ?float $default = null): float
    {
        $value = configFloat($key, $default);

        \assert($value !== null);

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
        $value = resolveConfig()->get($key, $default);

        \assert($value === null || \is_array($value));

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

        \assert($value !== null);

        return $value;
    }
}

if (! \function_exists('configString')) {
    /**
     * Config string resolver.
     */
    function configString(string $key, ?string $default = null, bool $trim = true): ?string
    {
        $value = resolveConfig()->get($key, $default);

        if ($value === null) {
            return null;
        }

        \assert(\is_string($value));

        if ($trim) {
            return \trim($value);
        }

        return $value;
    }
}

if (! \function_exists('mustConfigString')) {
    /**
     * Mandatory config string resolver.
     */
    function mustConfigString(string $key, ?string $default = null, bool $trim = true): string
    {
        $value = configString($key, $default, $trim);

        \assert($value !== null);

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
     * Resolve artisan.
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
    function resolveExceptionHandler(): Illuminate\Contracts\Debug\ExceptionHandler
    {
        return inject(Illuminate\Contracts\Debug\ExceptionHandler::class);
    }
}

if (! \function_exists('mustBeGuest')) {
    /**
     * Throw if authenticated.
     *
     * @param array<string|null> $guards
     */
    function mustBeGuest(array $guards = [null]): void
    {
        $authManager = resolveAuthManager();

        foreach ($guards as $guard) {
            if (! $authManager->guard($guard)->guest()) {
                throw new Tomchochola\Laratchi\Exceptions\MustBeGuestHttpException();
            }
        }
    }
}

if (! \function_exists('isGuest')) {
    /**
     * Check if authenticated.
     *
     * @param array<string|null> $guards
     */
    function isGuest(array $guards = [null]): bool
    {
        $authManager = resolveAuthManager();

        foreach ($guards as $guard) {
            if (! $authManager->guard($guard)->guest()) {
                return false;
            }
        }

        return true;
    }
}

if (! \function_exists('resolveUser')) {
    /**
     * Resolve user or null.
     *
     * @template T
     *
     * @param array<string|null> $guards
     * @param class-string<T> $template
     *
     * @return (T&Illuminate\Contracts\Auth\Authenticatable)|null
     */
    function resolveUser(array $guards = [null], string $template = Illuminate\Contracts\Auth\Authenticatable::class): ?Illuminate\Contracts\Auth\Authenticatable
    {
        $authManager = resolveAuthManager();

        foreach ($guards as $guard) {
            $user = $authManager->guard($guard)->user();

            if ($user !== null) {
                \assert($user instanceof $template);

                return $user;
            }
        }

        return null;
    }
}

if (! \function_exists('mustResolveUser')) {
    /**
     * Resolve user or throw.
     *
     * @template T
     *
     * @param array<string|null> $guards
     * @param class-string<T> $template
     *
     * @return T&Illuminate\Contracts\Auth\Authenticatable
     */
    function mustResolveUser(array $guards = [null], string $template = Illuminate\Contracts\Auth\Authenticatable::class): Illuminate\Contracts\Auth\Authenticatable
    {
        $authManager = resolveAuthManager();

        foreach ($guards as $guard) {
            $user = $authManager->guard($guard)->user();

            if ($user !== null) {
                \assert($user instanceof $template);

                return $user;
            }
        }

        throw new Illuminate\Auth\AuthenticationException();
    }
}

if (! \function_exists('requestSignature')) {
    /**
     * Make request signature.
     *
     * @param array<mixed> $data
     */
    function requestSignature(array $data = [], bool $defaults = true): Tomchochola\Laratchi\Http\Requests\RequestSignature
    {
        return new Tomchochola\Laratchi\Http\Requests\RequestSignature($data, $defaults);
    }
}

if (! \function_exists('envString')) {
    /**
     * Env string resolver.
     */
    function envString(string $key, ?string $default = null, bool $trim = true): ?string
    {
        $value = env($key, $default);

        if ($value === null) {
            return null;
        }

        \assert(\is_string($value));

        if ($trim) {
            return \trim($value);
        }

        return $value;
    }
}

if (! \function_exists('mustEnvString')) {
    /**
     * Mandatory env string resolver.
     */
    function mustEnvString(string $key, ?string $default = null, bool $trim = true): string
    {
        $value = envString($key, $default, $trim);

        \assert($value !== null);

        return $value;
    }
}

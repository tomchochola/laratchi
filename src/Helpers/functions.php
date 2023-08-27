<?php

declare(strict_types=1);

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Tomchochola\Laratchi\Config\Config;
use Tomchochola\Laratchi\Encoding\Csv;
use Tomchochola\Laratchi\Exceptions\Panicker;
use Tomchochola\Laratchi\Support\Resolver;
use Tomchochola\Laratchi\Support\Typer;
use Tomchochola\Laratchi\Translation\Trans;

if (! \function_exists('mustTransString')) {
    /**
     * Mandatory string translation resolver.
     *
     * @param array<string, string> $replace
     */
    function mustTransString(string $key, array $replace = [], ?string $locale = null, bool $fallback = true): string
    {
        return Trans::inject()->assertString($key, $replace, $locale, $fallback);
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
        return Trans::inject()->assertString($message, $replace, $locale, $fallback);
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
        return Trans::inject()->assertArray($key, $replace, $locale, $fallback);
    }
}

if (! \function_exists('configBool')) {
    /**
     * Config boolean resolver.
     *
     * @param array<bool|null> $in
     */
    function configBool(string $key, ?bool $default = null, array $in = []): ?bool
    {
        $value = resolveConfig()->get($key, $default) ?? $default;

        \assert($value === null || \is_bool($value), Panicker::message(__FUNCTION__, 'assertion failed', \compact('key', 'value', 'default')));

        if (\count($in) === 0 || \in_array($value, $in, true)) {
            return $value;
        }

        Panicker::panic(__FUNCTION__, 'is not in available options', \compact('key', 'value', 'in'));
    }
}

if (! \function_exists('mustConfigBool')) {
    /**
     * Mandatory config boolean resolver.
     *
     * @param array<bool> $in
     */
    function mustConfigBool(string $key, ?bool $default = null, array $in = []): bool
    {
        $value = configBool($key, $default, $in);

        \assert($value !== null, Panicker::message(__FUNCTION__, 'assertion failed', \compact('key', 'value', 'default')));

        return $value;
    }
}

if (! \function_exists('configInt')) {
    /**
     * Config int resolver.
     *
     * @param array<int|null> $in
     */
    function configInt(string $key, ?int $default = null, array $in = []): ?int
    {
        $value = resolveConfig()->get($key, $default) ?? $default;

        \assert($value === null || \is_int($value), Panicker::message(__FUNCTION__, 'assertion failed', \compact('key', 'value', 'default')));

        if (\count($in) === 0 || \in_array($value, $in, true)) {
            return $value;
        }

        Panicker::panic(__FUNCTION__, 'is not in available options', \compact('key', 'value', 'in'));
    }
}

if (! \function_exists('mustConfigInt')) {
    /**
     * Mandatory config int resolver.
     *
     * @param array<int> $in
     */
    function mustConfigInt(string $key, ?int $default = null, array $in = []): int
    {
        $value = configInt($key, $default, $in);

        \assert($value !== null, Panicker::message(__FUNCTION__, 'assertion failed', \compact('key', 'value', 'default')));

        return $value;
    }
}

if (! \function_exists('configFloat')) {
    /**
     * Config float resolver.
     *
     * @param array<float|null> $in
     */
    function configFloat(string $key, ?float $default = null, array $in = []): ?float
    {
        $value = resolveConfig()->get($key, $default) ?? $default;

        \assert($value === null || \is_float($value), Panicker::message(__FUNCTION__, 'assertion failed', \compact('key', 'value', 'default')));

        if (\count($in) === 0 || \in_array($value, $in, true)) {
            return $value;
        }

        Panicker::panic(__FUNCTION__, 'is not in available options', \compact('key', 'value', 'in'));
    }
}

if (! \function_exists('mustConfigFloat')) {
    /**
     * Mandatory config float resolver.
     *
     * @param array<float> $in
     */
    function mustConfigFloat(string $key, ?float $default = null, array $in = []): float
    {
        $value = configFloat($key, $default, $in);

        \assert($value !== null, Panicker::message(__FUNCTION__, 'assertion failed', \compact('key', 'value', 'default')));

        return $value;
    }
}

if (! \function_exists('configArray')) {
    /**
     * Config array resolver.
     *
     * @param array<array<mixed>|null> $default
     *
     * @return array<mixed>|null
     */
    function configArray(string $key, ?array $default = null): ?array
    {
        $value = resolveConfig()->get($key, $default) ?? $default;

        \assert($value === null || \is_array($value), Panicker::message(__FUNCTION__, 'assertion failed', \compact('key', 'value', 'default')));

        return $value;
    }
}

if (! \function_exists('mustConfigArray')) {
    /**
     * Mandatory config array resolver.
     *
     * @param array<array<mixed>> $default
     *
     * @return array<mixed>
     */
    function mustConfigArray(string $key, ?array $default = null): array
    {
        $value = configArray($key, $default);

        \assert($value !== null, Panicker::message(__FUNCTION__, 'assertion failed', \compact('key', 'value', 'default')));

        return $value;
    }
}

if (! \function_exists('configString')) {
    /**
     * Config string resolver.
     *
     * @param array<string|null> $in
     */
    function configString(string $key, ?string $default = null, array $in = []): ?string
    {
        $value = resolveConfig()->get($key, $default) ?? $default;

        \assert($value === null || \is_string($value), Panicker::message(__FUNCTION__, 'assertion failed', \compact('key', 'value', 'default')));

        if (\count($in) === 0 || \in_array($value, $in, true)) {
            return $value;
        }

        Panicker::panic(__FUNCTION__, 'is not in available options', \compact('key', 'value', 'in'));
    }
}

if (! \function_exists('mustConfigString')) {
    /**
     * Mandatory config string resolver.
     *
     * @param array<string> $in
     */
    function mustConfigString(string $key, ?string $default = null, array $in = []): string
    {
        $value = configString($key, $default, $in);

        \assert($value !== null, Panicker::message(__FUNCTION__, 'assertion failed', \compact('key', 'value', 'default')));

        return $value;
    }
}

if (! \function_exists('inject')) {
    /**
     * Resolve a service from the container.
     *
     * @template T of object
     *
     * @param class-string<T> $class
     * @param array<mixed> $parameters
     *
     * @return T
     */
    function inject(string $class, array $parameters = []): object
    {
        return Resolver::resolve($class, $parameters);
    }
}

if (! \function_exists('resolveApp')) {
    /**
     * Resolve app.
     */
    function resolveApp(): Illuminate\Foundation\Application
    {
        return Resolver::resolveApp();
    }
}

if (! \function_exists('resolveArtisan')) {
    /**
     * Resolve console kernel.
     */
    function resolveArtisan(): Illuminate\Foundation\Console\Kernel
    {
        return Resolver::resolveConsoleKernel();
    }
}

if (! \function_exists('resolveKernel')) {
    /**
     * Resolve HTTP kernel.
     */
    function resolveKernel(): Illuminate\Foundation\Http\Kernel
    {
        return Resolver::resolveHttpKernel();
    }
}

if (! \function_exists('resolveConsoleKernel')) {
    /**
     * Resolve console kernel.
     */
    function resolveConsoleKernel(): Illuminate\Foundation\Console\Kernel
    {
        return Resolver::resolveConsoleKernel();
    }
}

if (! \function_exists('resolveHttpKernel')) {
    /**
     * Resolve HTTP kernel.
     */
    function resolveHttpKernel(): Illuminate\Foundation\Http\Kernel
    {
        return Resolver::resolveHttpKernel();
    }
}

if (! \function_exists('resolveAuthManager')) {
    /**
     * Resolve auth manager.
     */
    function resolveAuthManager(): Illuminate\Auth\AuthManager
    {
        return Resolver::resolveAuthManager();
    }
}

if (! \function_exists('resolveGuard')) {
    /**
     * Resolve guard.
     */
    function resolveGuard(?string $name = null): Tomchochola\Laratchi\Auth\DatabaseTokenGuard
    {
        return Resolver::resolveDatabaseTokenGuard($name);
    }
}

if (! \function_exists('resolveUserProvider')) {
    /**
     * Resolve user provider.
     */
    function resolveUserProvider(?string $name = null): Illuminate\Auth\EloquentUserProvider
    {
        return Resolver::resolveEloquentUserProvider($name);
    }
}

if (! \function_exists('resolveBlade')) {
    /**
     * Resolve blade.
     */
    function resolveBlade(): Illuminate\View\Compilers\BladeCompiler
    {
        return Resolver::resolveBladeCompiler();
    }
}

if (! \function_exists('resolveBroadcastManager')) {
    /**
     * Resolve broadcast manager.
     */
    function resolveBroadcastManager(): Illuminate\Broadcasting\BroadcastManager
    {
        return Resolver::resolveBroadcastManager();
    }
}

if (! \function_exists('resolveBus')) {
    /**
     * Resolve bus.
     */
    function resolveBus(): Illuminate\Contracts\Bus\QueueingDispatcher
    {
        return Resolver::resolveQueueingDispatcher();
    }
}

if (! \function_exists('resolveCacheManager')) {
    /**
     * Resolve cache manager.
     */
    function resolveCacheManager(): Illuminate\Cache\CacheManager
    {
        return Resolver::resolveCacheManager();
    }
}

if (! \function_exists('resolveCache')) {
    /**
     * Resolve cache.
     */
    function resolveCache(?string $name = null): Illuminate\Contracts\Cache\Repository
    {
        return Resolver::resolveCacheManager()->store($name);
    }
}

if (! \function_exists('resolveConfig')) {
    /**
     * Resolve config.
     */
    function resolveConfig(): Illuminate\Config\Repository
    {
        return Resolver::resolveConfigRepository();
    }
}

if (! \function_exists('resolveCookieJar')) {
    /**
     * Resolve cookie.
     */
    function resolveCookieJar(): Illuminate\Cookie\CookieJar
    {
        return Resolver::resolveCookieJar();
    }
}

if (! \function_exists('resolveEncrypter')) {
    /**
     * Resolve encrypter.
     */
    function resolveEncrypter(): Illuminate\Encryption\Encrypter
    {
        return Resolver::resolveEncrypter();
    }
}

if (! \function_exists('resolveDatabaseManager')) {
    /**
     * Resolve database manager.
     */
    function resolveDatabaseManager(): Illuminate\Database\DatabaseManager
    {
        return Resolver::resolveDatabaseManager();
    }
}

if (! \function_exists('resolveDatabase')) {
    /**
     * Resolve database.
     */
    function resolveDatabase(?string $name = null): Illuminate\Database\Connection
    {
        return Resolver::resolveDatabaseManager()->connection($name);
    }
}

if (! \function_exists('resolveEventDispatcher')) {
    /**
     * Resolve event dispatcher.
     */
    function resolveEventDispatcher(): Illuminate\Contracts\Events\Dispatcher
    {
        return Resolver::resolveEventDispatcher();
    }
}

if (! \function_exists('resolveFilesystem')) {
    /**
     * Resolve filesystem.
     */
    function resolveFilesystem(): Illuminate\Filesystem\Filesystem
    {
        return Resolver::resolveFilesystem();
    }
}

if (! \function_exists('resolveGate')) {
    /**
     * Resolve gate.
     */
    function resolveGate(): Illuminate\Auth\Access\Gate
    {
        return Resolver::resolveGate();
    }
}

if (! \function_exists('resolveHashManager')) {
    /**
     * Resolve hash manager.
     */
    function resolveHashManager(): Illuminate\Hashing\HashManager
    {
        return Resolver::resolveHashManager();
    }
}

if (! \function_exists('resolveHasher')) {
    /**
     * Resolve hasher.
     */
    function resolveHasher(?string $driver = null): Illuminate\Contracts\Hashing\Hasher
    {
        return Resolver::resolveHasher($driver);
    }
}

if (! \function_exists('resolveHttp')) {
    /**
     * Resolve HTTP client.
     */
    function resolveHttp(): Illuminate\Http\Client\Factory
    {
        return Resolver::resolveHttpClientFactory();
    }
}

if (! \function_exists('resolveTranslator')) {
    /**
     * Resolve translator.
     */
    function resolveTranslator(): Illuminate\Translation\Translator
    {
        return Resolver::resolveTranslator();
    }
}

if (! \function_exists('resolveLogger')) {
    /**
     * Resolve logger.
     */
    function resolveLogger(): Illuminate\Log\LogManager
    {
        return Resolver::resolveLogManager();
    }
}

if (! \function_exists('resolveMailManager')) {
    /**
     * Resolve mail manager.
     */
    function resolveMailManager(): Illuminate\Contracts\Mail\Factory
    {
        return Resolver::resolveMailFactory();
    }
}

if (! \function_exists('resolveNotificator')) {
    /**
     * Resolve notification manager.
     */
    function resolveNotificator(): Illuminate\Contracts\Notifications\Factory
    {
        return Resolver::resolveNotificationFactory();
    }
}

if (! \function_exists('resolveParallelTesting')) {
    /**
     * Resolve parallel testing.
     */
    function resolveParallelTesting(): Illuminate\Testing\ParallelTesting
    {
        return Resolver::resolveParallelTesting();
    }
}

if (! \function_exists('resolvePasswordBrokerManager')) {
    /**
     * Resolve password broker manager.
     */
    function resolvePasswordBrokerManager(): Illuminate\Auth\Passwords\PasswordBrokerManager
    {
        return Resolver::resolvePasswordBrokerManager();
    }
}

if (! \function_exists('resolvePasswordBroker')) {
    /**
     * Resolve password broker.
     */
    function resolvePasswordBroker(?string $name = null): Illuminate\Auth\Passwords\PasswordBroker
    {
        return Resolver::resolvePasswordBroker($name);
    }
}

if (! \function_exists('resolvePasswordTokenRepository')) {
    /**
     * Resolve password token repository.
     */
    function resolvePasswordTokenRepository(?string $name = null): Illuminate\Auth\Passwords\DatabaseTokenRepository
    {
        return Resolver::resolveDatabaseTokenRepository($name);
    }
}

if (! \function_exists('resolveQueueManager')) {
    /**
     * Resolve queue manager.
     */
    function resolveQueueManager(): Illuminate\Queue\QueueManager
    {
        return Resolver::resolveQueueManager();
    }
}

if (! \function_exists('resolveRateLimiter')) {
    /**
     * Resolve rate limiter.
     */
    function resolveRateLimiter(): Illuminate\Cache\RateLimiter
    {
        return Resolver::resolveRateLimiter();
    }
}

if (! \function_exists('resolveRedirector')) {
    /**
     * Resolve redirector.
     */
    function resolveRedirector(): Illuminate\Routing\Redirector
    {
        return Resolver::resolveRedirector();
    }
}

if (! \function_exists('resolveRedisManager')) {
    /**
     * Resolve redis manager.
     */
    function resolveRedisManager(): Illuminate\Redis\RedisManager
    {
        return Resolver::resolveRedisManager();
    }
}

if (! \function_exists('resolveRequest')) {
    /**
     * Resolve request.
     */
    function resolveRequest(): Illuminate\Http\Request
    {
        return Resolver::resolveRequest();
    }
}

if (! \function_exists('resolveResponseFactory')) {
    /**
     * Resolve response factory.
     */
    function resolveResponseFactory(): Illuminate\Routing\ResponseFactory
    {
        return Resolver::resolveResponseFactory();
    }
}

if (! \function_exists('resolveRouter')) {
    /**
     * Resolve router.
     */
    function resolveRouter(): Illuminate\Routing\Router
    {
        return Resolver::resolveRouter();
    }
}

if (! \function_exists('resolveRouteRegistrar')) {
    /**
     * Resolve route registrar.
     */
    function resolveRouteRegistrar(): Illuminate\Routing\RouteRegistrar
    {
        return Resolver::resolveRouteRegistrar();
    }
}

if (! \function_exists('resolveSchema')) {
    /**
     * Resolve schema.
     */
    function resolveSchema(): Illuminate\Database\Schema\Builder
    {
        return Resolver::resolveSchemaBuilder();
    }
}

if (! \function_exists('resolveSessionManager')) {
    /**
     * Resolve session manager.
     */
    function resolveSessionManager(): Illuminate\Session\SessionManager
    {
        return Resolver::resolveSessionManager();
    }
}

if (! \function_exists('resolveSession')) {
    /**
     * Resolve session.
     */
    function resolveSession(?string $driver = null): Illuminate\Session\Store
    {
        return Resolver::resolveSessionStore($driver);
    }
}

if (! \function_exists('resolveFilesystemManager')) {
    /**
     * Resolve filesystem manager.
     */
    function resolveFilesystemManager(): Illuminate\Filesystem\FilesystemManager
    {
        return Resolver::resolveFilesystemManager();
    }
}

if (! \function_exists('resolveUrlFactory')) {
    /**
     * Resolve url factory.
     */
    function resolveUrlFactory(): Illuminate\Routing\UrlGenerator
    {
        return Resolver::resolveUrlGenerator();
    }
}

if (! \function_exists('resolveValidatorFactory')) {
    /**
     * Resolve validator factory.
     */
    function resolveValidatorFactory(): Illuminate\Validation\Factory
    {
        return Resolver::resolveValidatorFactory();
    }
}

if (! \function_exists('resolveViewFactory')) {
    /**
     * Resolve view factory.
     */
    function resolveViewFactory(): Illuminate\View\Factory
    {
        return Resolver::resolveViewFactory();
    }
}

if (! \function_exists('resolveExceptionHandler')) {
    /**
     * Resolve exception handler.
     */
    function resolveExceptionHandler(): Illuminate\Foundation\Exceptions\Handler
    {
        return Resolver::resolveExceptionHandler();
    }
}

if (! \function_exists('resolveDate')) {
    /**
     * Resolve date factory.
     */
    function resolveDate(): Illuminate\Support\DateFactory
    {
        return Resolver::resolveDateFactory();
    }
}

if (! \function_exists('resolveNow')) {
    /**
     * Resolve now.
     */
    function resolveNow(): Illuminate\Support\Carbon
    {
        return Resolver::resolveDateFactory()->now();
    }
}

if (! \function_exists('resolveMix')) {
    /**
     * Resolve mix.
     */
    function resolveMix(): Illuminate\Foundation\Mix
    {
        return Resolver::resolveMix();
    }
}

if (! \function_exists('resolveVite')) {
    /**
     * Resolve vite.
     */
    function resolveVite(): Illuminate\Foundation\Vite
    {
        return Resolver::resolveVite();
    }
}

if (! \function_exists('unsafeEnv')) {
    /**
     * Unsafe env resolver.
     */
    function unsafeEnv(string $key, mixed $default = null): mixed
    {
        if (Resolver::resolveApp()->bound('env')) {
            Panicker::panic(__FUNCTION__, 'env is already bound');
        }

        return Illuminate\Support\Env::get($key, $default);
    }
}

if (! \function_exists('envString')) {
    /**
     * Env string resolver.
     *
     * @param array<string|null> $in
     */
    function envString(string $key, ?string $default = null, bool $trim = true, array $in = []): ?string
    {
        $value = unsafeEnv($key, $default) ?? $default;

        if ($value === null) {
            return null;
        }

        $value = \filter_var($value);

        \assert($value !== false, Panicker::message(__FUNCTION__, 'assertion failed', \compact('key', 'value', 'default')));

        if ($trim) {
            $value = \trim($value);
        }

        if ($value === '') {
            $value = $trim && $default !== null ? \trim($default) : $default;
        }

        if ($value === null || $value === '') {
            return null;
        }

        if (\count($in) === 0 || \in_array($value, $in, true)) {
            return $value;
        }

        Panicker::panic(__FUNCTION__, 'is not in available options', \compact('key', 'value', 'in'));
    }
}

if (! \function_exists('mustEnvString')) {
    /**
     * Mandatory env string resolver.
     *
     * @param array<string> $in
     */
    function mustEnvString(string $key, ?string $default = null, bool $trim = true, array $in = []): string
    {
        $value = envString($key, $default, $trim, $in);

        \assert($value !== null, Panicker::message(__FUNCTION__, 'assertion failed', \compact('key', 'value', 'default')));

        return $value;
    }
}

if (! \function_exists('envBool')) {
    /**
     * Env bool resolver.
     *
     * @param array<bool|null> $in
     */
    function envBool(string $key, ?bool $default = null, array $in = []): ?bool
    {
        $value = unsafeEnv($key, $default) ?? $default;

        if ($value === null) {
            return null;
        }

        $value = \filter_var($value, \FILTER_VALIDATE_BOOL, \FILTER_NULL_ON_FAILURE);

        \assert($value !== null, Panicker::message(__FUNCTION__, 'assertion failed', \compact('key', 'value', 'default')));

        if (\count($in) === 0 || \in_array($value, $in, true)) {
            return $value;
        }

        Panicker::panic(__FUNCTION__, 'is not in available options', \compact('key', 'value', 'in'));
    }
}

if (! \function_exists('mustEnvBool')) {
    /**
     * Mandatory env bool resolver.
     *
     * @param array<bool> $in
     */
    function mustEnvBool(string $key, ?bool $default = null, array $in = []): bool
    {
        $value = envBool($key, $default, $in);

        \assert($value !== null, Panicker::message(__FUNCTION__, 'assertion failed', \compact('key', 'value', 'default')));

        return $value;
    }
}

if (! \function_exists('envInt')) {
    /**
     * Env int resolver.
     *
     * @param array<int|null> $in
     */
    function envInt(string $key, ?int $default = null, array $in = []): ?int
    {
        $value = unsafeEnv($key, $default) ?? $default;

        if ($value === null) {
            return null;
        }

        $value = \filter_var($value, \FILTER_VALIDATE_INT);

        \assert($value !== false, Panicker::message(__FUNCTION__, 'assertion failed', \compact('key', 'value', 'default')));

        if (\count($in) === 0 || \in_array($value, $in, true)) {
            return $value;
        }

        Panicker::panic(__FUNCTION__, 'is not in available options', \compact('key', 'value', 'in'));
    }
}

if (! \function_exists('mustEnvInt')) {
    /**
     * Mandatory env int resolver.
     *
     * @param array<int> $in
     */
    function mustEnvInt(string $key, ?int $default = null, array $in = []): int
    {
        $value = envInt($key, $default, $in);

        \assert($value !== null, Panicker::message(__FUNCTION__, 'assertion failed', \compact('key', 'value', 'default')));

        return $value;
    }
}

if (! \function_exists('envFloat')) {
    /**
     * Env float resolver.
     *
     * @param array<float|null> $in
     */
    function envFloat(string $key, ?float $default = null, array $in = []): ?float
    {
        $value = unsafeEnv($key, $default) ?? $default;

        if ($value === null) {
            return null;
        }

        $value = \filter_var($value, \FILTER_VALIDATE_FLOAT);

        \assert($value !== false, Panicker::message(__FUNCTION__, 'assertion failed', \compact('key', 'value', 'default')));

        if (\count($in) === 0 || \in_array($value, $in, true)) {
            return $value;
        }

        Panicker::panic(__FUNCTION__, 'is not in available options', \compact('key', 'value', 'in'));
    }
}

if (! \function_exists('mustEnvFloat')) {
    /**
     * Mandatory env float resolver.
     *
     * @param array<float> $in
     */
    function mustEnvFloat(string $key, ?float $default = null, array $in = []): float
    {
        $value = envFloat($key, $default, $in);

        \assert($value !== null, Panicker::message(__FUNCTION__, 'assertion failed', \compact('key', 'value', 'default')));

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

        if (\in_array($value, ['local', 'testing', 'development', 'staging', 'production'], true)) {
            return $value;
        }

        Panicker::panic(__FUNCTION__, 'is not in available options', \compact('value'));
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
     * @template A
     * @template B
     * @template C
     * @template D
     * @template E
     *
     * @param array{local: A, testing: B, development: C, staging: D, production: E} $mapping
     *
     * @return A|B|C|D|E
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
        Panicker::panic(__FUNCTION__, $message);
    }
}

if (! \function_exists('assertNeverIf')) {
    /**
     * Assert never if.
     */
    function assertNeverIf(bool $pass, string $message = 'assert never if'): void
    {
        if ($pass) {
            Panicker::panic(__FUNCTION__, $message);
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
            Panicker::panic(__FUNCTION__, $message);
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
            Panicker::panic(__FUNCTION__, $message);
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
        return Csv::line($data);
    }
}

if (! \function_exists('locale')) {
    /**
     * Locale.
     */
    function locale(): string
    {
        return Config::inject()->appLocale();
    }
}

if (! \function_exists('locales')) {
    /**
     * Locales.
     *
     * @return array<int, string>
     */
    function locales(): array
    {
        return Config::inject()->appLocales();
    }
}

if (! \function_exists('assertString')) {
    /**
     * Assert string.
     */
    function assertString(mixed $value): string
    {
        return Typer::assertString($value);
    }
}

if (! \function_exists('assertNullableString')) {
    /**
     * Assert nullable string.
     */
    function assertNullableString(mixed $value): ?string
    {
        return Typer::assertNullableString($value);
    }
}

if (! \function_exists('assertBool')) {
    /**
     * Assert bool.
     */
    function assertBool(mixed $value): bool
    {
        return Typer::assertBool($value);
    }
}

if (! \function_exists('assertNullableBool')) {
    /**
     * Assert nullable bool.
     */
    function assertNullableBool(mixed $value): ?bool
    {
        return Typer::assertNullableBool($value);
    }
}

if (! \function_exists('assertInt')) {
    /**
     * Assert int.
     */
    function assertInt(mixed $value): int
    {
        return Typer::assertInt($value);
    }
}

if (! \function_exists('assertNullableInt')) {
    /**
     * Assert nullable int.
     */
    function assertNullableInt(mixed $value): ?int
    {
        return Typer::assertNullableInt($value);
    }
}

if (! \function_exists('assertFloat')) {
    /**
     * Assert float.
     */
    function assertFloat(mixed $value): float
    {
        return Typer::assertFloat($value);
    }
}

if (! \function_exists('assertNullableFloat')) {
    /**
     * Assert nullable float.
     */
    function assertNullableFloat(mixed $value): ?float
    {
        return Typer::assertNullableFloat($value);
    }
}

if (! \function_exists('assertArray')) {
    /**
     * Assert array.
     *
     * @return array<mixed>
     */
    function assertArray(mixed $value): array
    {
        return Typer::assertArray($value);
    }
}

if (! \function_exists('assertNullableArray')) {
    /**
     * Assert nullable array.
     *
     * @return array<mixed>|null
     */
    function assertNullableArray(mixed $value): ?array
    {
        return Typer::assertNullableArray($value);
    }
}

if (! \function_exists('assertFile')) {
    /**
     * Assert file.
     */
    function assertFile(mixed $value): UploadedFile
    {
        return Typer::assertFile($value);
    }
}

if (! \function_exists('assertNullableFile')) {
    /**
     * Assert nullable file.
     */
    function assertNullableFile(mixed $value): ?UploadedFile
    {
        return Typer::assertNullableFile($value);
    }
}

if (! \function_exists('assertCarbon')) {
    /**
     * Assert carbon.
     */
    function assertCarbon(mixed $value): Carbon
    {
        return Typer::assertCarbon($value);
    }
}

if (! \function_exists('assertNullableCarbon')) {
    /**
     * Assert nullable carbon.
     */
    function assertNullableCarbon(mixed $value): ?Carbon
    {
        return Typer::assertNullableCarbon($value);
    }
}

if (! \function_exists('assertObject')) {
    /**
     * Assert object.
     */
    function assertObject(mixed $value): object
    {
        return Typer::assertObject($value);
    }
}

if (! \function_exists('assertNullableObject')) {
    /**
     * Assert nullable object.
     */
    function assertNullableObject(mixed $value): ?object
    {
        return Typer::assertNullableObject($value);
    }
}

if (! \function_exists('assertScalar')) {
    /**
     * Assert scalar.
     *
     * @return scalar
     */
    function assertScalar(mixed $value): int|float|string|bool
    {
        return Typer::assertScalar($value);
    }
}

if (! \function_exists('assertNullableScalar')) {
    /**
     * Assert nullable scalar.
     *
     * @return scalar|null
     */
    function assertNullableScalar(mixed $value): int|float|string|bool|null
    {
        return Typer::assertNullableScalar($value);
    }
}

if (! \function_exists('assertEnum')) {
    /**
     * Assert enum.
     *
     * @template T of BackedEnum
     *
     * @param class-string<T> $enum
     *
     * @return T
     */
    function assertEnum(mixed $value, string $enum): BackedEnum
    {
        return Typer::assertEnum($value, $enum);
    }
}

if (! \function_exists('assertNullableEnum')) {
    /**
     * Assert nullable enum.
     *
     * @template T of BackedEnum
     *
     * @param class-string<T> $enum
     *
     * @return T|null
     */
    function assertNullableEnum(mixed $value, string $enum): ?BackedEnum
    {
        return Typer::assertNullableEnum($value, $enum);
    }
}

if (! \function_exists('assertInstance')) {
    /**
     * Assert instance.
     *
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    function assertInstance(mixed $value, string $class): object
    {
        return Typer::assertInstance($value, $class);
    }
}

if (! \function_exists('assertNullableInstance')) {
    /**
     * Assert nullable instance.
     *
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return T|null
     */
    function assertNullableInstance(mixed $value, string $class): ?object
    {
        return Typer::assertNullableInstance($value, $class);
    }
}

if (! \function_exists('assertA')) {
    /**
     * Assert a.
     *
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return class-string<T>
     */
    function assertA(mixed $value, string $class): string
    {
        return Typer::assertA($value, $class);
    }
}

if (! \function_exists('assertNullableA')) {
    /**
     * Assert nullable a.
     *
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return class-string<T>|null
     */
    function assertNullableA(mixed $value, string $class): ?string
    {
        return Typer::assertNullableA($value, $class);
    }
}

if (! \function_exists('parseString')) {
    /**
     * Parse string.
     */
    function parseString(mixed $value): string
    {
        return Typer::parseString($value);
    }
}

if (! \function_exists('parseNullableString')) {
    /**
     * Parse nullable string.
     */
    function parseNullableString(mixed $value): ?string
    {
        return Typer::parseNullableString($value);
    }
}

if (! \function_exists('mustParseString')) {
    /**
     * Must parse string.
     */
    function mustParseString(mixed $value): string
    {
        return Typer::mustParseString($value);
    }
}

if (! \function_exists('mustParseNullableString')) {
    /**
     * Must parse nullable string.
     */
    function mustParseNullableString(mixed $value): ?string
    {
        return Typer::mustParseNullableString($value);
    }
}

if (! \function_exists('parseBool')) {
    /**
     * Parse bool.
     */
    function parseBool(mixed $value): bool
    {
        return Typer::parseBool($value);
    }
}

if (! \function_exists('parseNullableBool')) {
    /**
     * Parse nullable bool.
     */
    function parseNullableBool(mixed $value): ?bool
    {
        return Typer::parseNullableBool($value);
    }
}

if (! \function_exists('mustParseBool')) {
    /**
     * Must parse bool.
     */
    function mustParseBool(mixed $value): bool
    {
        return Typer::mustParseBool($value);
    }
}

if (! \function_exists('mustParseNullableBool')) {
    /**
     * Must parse nullable bool.
     */
    function mustParseNullableBool(mixed $value): ?bool
    {
        return Typer::mustParseNullableBool($value);
    }
}

if (! \function_exists('parseInt')) {
    /**
     * Parse int.
     */
    function parseInt(mixed $value): int
    {
        return Typer::parseInt($value);
    }
}

if (! \function_exists('parseNullableInt')) {
    /**
     * Parse nullable int.
     */
    function parseNullableInt(mixed $value): ?int
    {
        return Typer::parseNullableInt($value);
    }
}

if (! \function_exists('mustParseInt')) {
    /**
     * Must parse int.
     */
    function mustParseInt(mixed $value): int
    {
        return Typer::mustParseInt($value);
    }
}

if (! \function_exists('mustParseNullableInt')) {
    /**
     * Must parse nullable int.
     */
    function mustParseNullableInt(mixed $value): ?int
    {
        return Typer::mustParseNullableInt($value);
    }
}

if (! \function_exists('parseFloat')) {
    /**
     * Parse float.
     */
    function parseFloat(mixed $value): float
    {
        return Typer::parseFloat($value);
    }
}

if (! \function_exists('parseNullableFloat')) {
    /**
     * Parse nullable float.
     */
    function parseNullableFloat(mixed $value): ?float
    {
        return Typer::parseNullableFloat($value);
    }
}

if (! \function_exists('mustParseFloat')) {
    /**
     * Must parse float.
     */
    function mustParseFloat(mixed $value): float
    {
        return Typer::mustParseFloat($value);
    }
}

if (! \function_exists('mustParseNullableFloat')) {
    /**
     * Must parse nullable float.
     */
    function mustParseNullableFloat(mixed $value): ?float
    {
        return Typer::mustParseNullableFloat($value);
    }
}

if (! \function_exists('parseArray')) {
    /**
     * Parse array.
     *
     * @return array<mixed>
     */
    function parseArray(mixed $value): array
    {
        return Typer::parseArray($value);
    }
}

if (! \function_exists('parseNullableArray')) {
    /**
     * Parse nullable array.
     *
     * @return array<mixed>|null
     */
    function parseNullableArray(mixed $value): ?array
    {
        return Typer::parseNullableArray($value);
    }
}

if (! \function_exists('mustParseArray')) {
    /**
     * Must parse array.
     *
     * @return array<mixed>
     */
    function mustParseArray(mixed $value): array
    {
        return Typer::mustParseArray($value);
    }
}

if (! \function_exists('mustParseNullableArray')) {
    /**
     * Must parse nullable array.
     *
     * @return array<mixed>|null
     */
    function mustParseNullableArray(mixed $value): ?array
    {
        return Typer::mustParseNullableArray($value);
    }
}

if (! \function_exists('parseFile')) {
    /**
     * Parse file.
     */
    function parseFile(mixed $value): UploadedFile
    {
        return Typer::parseFile($value);
    }
}

if (! \function_exists('parseNullableFile')) {
    /**
     * Parse nullable file.
     */
    function parseNullableFile(mixed $value): ?UploadedFile
    {
        return Typer::parseNullableFile($value);
    }
}

if (! \function_exists('mustParseFile')) {
    /**
     * Must parse file.
     */
    function mustParseFile(mixed $value): UploadedFile
    {
        return Typer::mustParseFile($value);
    }
}

if (! \function_exists('mustParseNullableFile')) {
    /**
     * Must parse nullable file.
     */
    function mustParseNullableFile(mixed $value): ?UploadedFile
    {
        return Typer::mustParseNullableFile($value);
    }
}

if (! \function_exists('parseCarbon')) {
    /**
     * Parse carbon.
     */
    function parseCarbon(mixed $value, ?string $format = null, ?string $tz = null): Carbon
    {
        return Typer::parseCarbon($value, $format, $tz);
    }
}

if (! \function_exists('parseNullableCarbon')) {
    /**
     * Parse nullable carbon.
     */
    function parseNullableCarbon(mixed $value, ?string $format = null, ?string $tz = null): ?Carbon
    {
        return Typer::parseNullableCarbon($value, $format, $tz);
    }
}

if (! \function_exists('mustParseCarbon')) {
    /**
     * Must parse carbon.
     */
    function mustParseCarbon(mixed $value, ?string $format = null, ?string $tz = null): Carbon
    {
        return Typer::mustParseCarbon($value, $format, $tz);
    }
}

if (! \function_exists('mustParseNullableCarbon')) {
    /**
     * Must parse nullable carbon.
     */
    function mustParseNullableCarbon(mixed $value, ?string $format = null, ?string $tz = null): ?Carbon
    {
        return Typer::mustParseNullableCarbon($value, $format, $tz);
    }
}

if (! \function_exists('parseObject')) {
    /**
     * Parse object.
     */
    function parseObject(mixed $value): object
    {
        return Typer::parseObject($value);
    }
}

if (! \function_exists('parseNullableObject')) {
    /**
     * Parse nullable object.
     */
    function parseNullableObject(mixed $value): ?object
    {
        return Typer::parseNullableObject($value);
    }
}

if (! \function_exists('mustParseObject')) {
    /**
     * Must parse object.
     */
    function mustParseObject(mixed $value): object
    {
        return Typer::mustParseObject($value);
    }
}

if (! \function_exists('mustParseNullableObject')) {
    /**
     * Must parse nullable object.
     */
    function mustParseNullableObject(mixed $value): ?object
    {
        return Typer::mustParseNullableObject($value);
    }
}

if (! \function_exists('parseScalar')) {
    /**
     * Parse scalar.
     */
    function parseScalar(mixed $value): string|int|float|bool
    {
        return Typer::parseScalar($value);
    }
}

if (! \function_exists('parseNullableScalar')) {
    /**
     * Parse nullable scalar.
     */
    function parseNullableScalar(mixed $value): string|int|float|bool|null
    {
        return Typer::parseNullableScalar($value);
    }
}

if (! \function_exists('mustParseScalar')) {
    /**
     * Must parse scalar.
     */
    function mustParseScalar(mixed $value): string|int|float|bool
    {
        return Typer::mustParseScalar($value);
    }
}

if (! \function_exists('mustParseNullableScalar')) {
    /**
     * Must parse nullable scalar.
     */
    function mustParseNullableScalar(mixed $value): string|int|float|bool|null
    {
        return Typer::mustParseNullableScalar($value);
    }
}

if (! \function_exists('parseEnum')) {
    /**
     * Parse enum.
     *
     * @template T of BackedEnum
     *
     * @param class-string<T> $enum
     *
     * @return T
     */
    function parseEnum(mixed $value, string $enum): BackedEnum
    {
        return Typer::parseEnum($value, $enum);
    }
}

if (! \function_exists('parseNullableEnum')) {
    /**
     * Parse nullable enum.
     *
     * @template T of BackedEnum
     *
     * @param class-string<T> $enum
     *
     * @return T|null
     */
    function parseNullableEnum(mixed $value, string $enum): ?BackedEnum
    {
        return Typer::parseNullableEnum($value, $enum);
    }
}

if (! \function_exists('mustParseEnum')) {
    /**
     * Must parse enum.
     *
     * @template T of BackedEnum
     *
     * @param class-string<T> $enum
     *
     * @return T
     */
    function mustParseEnum(mixed $value, string $enum): BackedEnum
    {
        return Typer::mustParseEnum($value, $enum);
    }
}

if (! \function_exists('mustParseNullableEnum')) {
    /**
     * Must parse nullable enum.
     *
     * @template T of BackedEnum
     *
     * @param class-string<T> $enum
     *
     * @return T|null
     */
    function mustParseNullableEnum(mixed $value, string $enum): ?BackedEnum
    {
        return Typer::mustParseNullableEnum($value, $enum);
    }
}

if (! \function_exists('assertIn')) {
    /**
     * Assert in.
     *
     * @template T
     *
     * @param T $value
     * @param array<mixed> $enum
     *
     * @return T
     */
    function assertIn(mixed $value, array $enum): mixed
    {
        return Typer::assertIn($value, $enum);
    }
}

if (! \function_exists('assertNotNull')) {
    /**
     * Assert not null.
     *
     * @template T of string|int|float|bool|object|array
     *
     * @param T|null $value
     *
     * @return T
     */
    function assertNotNull(mixed $value): string|int|float|bool|object|array
    {
        return Typer::assertNotNull($value);
    }
}

if (! \function_exists('assertNull')) {
    /**
     * Assert null.
     */
    function assertNull(mixed $value): mixed
    {
        return Typer::assertNull($value);
    }
}

if (! \function_exists('parseIntEnum')) {
    /**
     * Parse int enum.
     *
     * @template T of BackedEnum
     *
     * @param class-string<T> $enum
     *
     * @return T
     */
    function parseIntEnum(mixed $value, string $enum): BackedEnum
    {
        return Typer::parseIntEnum($value, $enum);
    }
}

if (! \function_exists('parseNullableIntEnum')) {
    /**
     * Parse nullable int enum.
     *
     * @template T of BackedEnum
     *
     * @param class-string<T> $enum
     *
     * @return T|null
     */
    function parseNullableIntEnum(mixed $value, string $enum): ?BackedEnum
    {
        return Typer::parseNullableIntEnum($value, $enum);
    }
}

if (! \function_exists('mustParseIntEnum')) {
    /**
     * Must parse int enum.
     *
     * @template T of BackedEnum
     *
     * @param class-string<T> $enum
     *
     * @return T
     */
    function mustParseIntEnum(mixed $value, string $enum): BackedEnum
    {
        return Typer::mustParseIntEnum($value, $enum);
    }
}

if (! \function_exists('mustParseNullableIntEnum')) {
    /**
     * Must parse nullable int enum.
     *
     * @template T of BackedEnum
     *
     * @param class-string<T> $enum
     *
     * @return T|null
     */
    function mustParseNullableIntEnum(mixed $value, string $enum): ?BackedEnum
    {
        return Typer::mustParseNullableIntEnum($value, $enum);
    }
}

if (! \function_exists('parseStringEnum')) {
    /**
     * Parse string enum.
     *
     * @template T of BackedEnum
     *
     * @param class-string<T> $enum
     *
     * @return T
     */
    function parseStringEnum(mixed $value, string $enum): BackedEnum
    {
        return Typer::parseStringEnum($value, $enum);
    }
}

if (! \function_exists('parseNullableStringEnum')) {
    /**
     * Parse nullable string enum.
     *
     * @template T of BackedEnum
     *
     * @param class-string<T> $enum
     *
     * @return T|null
     */
    function parseNullableStringEnum(mixed $value, string $enum): ?BackedEnum
    {
        return Typer::parseNullableStringEnum($value, $enum);
    }
}

if (! \function_exists('mustParseStringEnum')) {
    /**
     * Must parse string enum.
     *
     * @template T of BackedEnum
     *
     * @param class-string<T> $enum
     *
     * @return T
     */
    function mustParseStringEnum(mixed $value, string $enum): BackedEnum
    {
        return Typer::mustParseStringEnum($value, $enum);
    }
}

if (! \function_exists('mustParseNullableStringEnum')) {
    /**
     * Must parse nullable string enum.
     *
     * @template T of BackedEnum
     *
     * @param class-string<T> $enum
     *
     * @return T|null
     */
    function mustParseNullableStringEnum(mixed $value, string $enum): ?BackedEnum
    {
        return Typer::mustParseNullableStringEnum($value, $enum);
    }
}

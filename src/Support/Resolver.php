<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Support;

use Illuminate\Auth\Access\Gate;
use Illuminate\Auth\AuthManager;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Auth\Passwords\DatabaseTokenRepository;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Auth\Passwords\PasswordBrokerManager;
use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Cache\CacheManager;
use Illuminate\Cache\RateLimiter;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Bus\QueueingDispatcher as QueueingDispatcherContract;
use Illuminate\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcherContract;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Illuminate\Contracts\Http\Kernel as HttpKernelContract;
use Illuminate\Contracts\Mail\Factory as MailFactoryContract;
use Illuminate\Contracts\Notifications\Factory as NotificationFactoryContract;
use Illuminate\Cookie\CookieJar;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Schema\Builder as SchemaBuilder;
use Illuminate\Encryption\Encrypter;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Mix;
use Illuminate\Foundation\Vite;
use Illuminate\Hashing\HashManager;
use Illuminate\Http\Client\Factory as HttpClientFactory;
use Illuminate\Http\Request;
use Illuminate\Log\LogManager;
use Illuminate\Queue\QueueManager;
use Illuminate\Redis\RedisManager;
use Illuminate\Routing\Redirector;
use Illuminate\Routing\ResponseFactory;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Routing\RouteRegistrar;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Session\SessionManager;
use Illuminate\Session\Store as SessionStore;
use Illuminate\Support\DateFactory;
use Illuminate\Support\Facades\Artisan as ArtisanFacade;
use Illuminate\Support\Facades\Auth as AuthFacade;
use Illuminate\Support\Facades\Blade as BladeFacade;
use Illuminate\Support\Facades\Broadcast as BroadcastFacade;
use Illuminate\Support\Facades\Bus as BusFacade;
use Illuminate\Support\Facades\Cache as CacheFacade;
use Illuminate\Support\Facades\Config as ConfigFacade;
use Illuminate\Support\Facades\Cookie as CookieFacade;
use Illuminate\Support\Facades\Crypt as CryptFacade;
use Illuminate\Support\Facades\Date as DateFacade;
use Illuminate\Support\Facades\DB as DBFacade;
use Illuminate\Support\Facades\Event as EventFacade;
use Illuminate\Support\Facades\File as FileFacade;
use Illuminate\Support\Facades\Gate as GateFacade;
use Illuminate\Support\Facades\Hash as HashFacade;
use Illuminate\Support\Facades\Http as HttpFacade;
use Illuminate\Support\Facades\Lang as LangFacade;
use Illuminate\Support\Facades\Log as LogFacade;
use Illuminate\Support\Facades\Mail as MailFacade;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Illuminate\Support\Facades\ParallelTesting as ParallelTestingFacade;
use Illuminate\Support\Facades\Password as PasswordFacade;
use Illuminate\Support\Facades\Queue as QueueFacade;
use Illuminate\Support\Facades\RateLimiter as RateLimiterFacade;
use Illuminate\Support\Facades\Redirect as RedirectFacade;
use Illuminate\Support\Facades\Redis as RedisFacade;
use Illuminate\Support\Facades\Request as RequestFacade;
use Illuminate\Support\Facades\Response as ResponseFacade;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Support\Facades\Schema as SchemaFacade;
use Illuminate\Support\Facades\Session as SessionFacade;
use Illuminate\Support\Facades\Storage as StorageFacade;
use Illuminate\Support\Facades\URL as URLFacade;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\Testing\ParallelTesting;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory as ValidationFactory;
use Illuminate\Validation\PresenceVerifierInterface;
use Illuminate\Validation\Validator;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Factory as ViewFactory;
use Tomchochola\Laratchi\Auth\DatabaseTokenGuard;
use Tomchochola\Laratchi\Validation\SecureValidationFactory;

class Resolver
{
    /**
     * Resolve app.
     */
    public static function resolveApp(): Application
    {
        return Application::getInstance();
    }

    /**
     * Resolve console kernel.
     */
    public static function resolveConsoleKernel(): ConsoleKernel
    {
        return assertInstance(ArtisanFacade::getFacadeRoot(), ConsoleKernel::class);
    }

    /**
     * Resolve auth manager.
     */
    public static function resolveAuthManager(): AuthManager
    {
        return assertInstance(AuthFacade::getFacadeRoot(), AuthManager::class);
    }

    /**
     * Resolve blade compiler.
     */
    public static function resolveBladeCompiler(): BladeCompiler
    {
        return assertInstance(BladeFacade::getFacadeRoot(), BladeCompiler::class);
    }

    /**
     * Resolve broadcast manager.
     */
    public static function resolveBroadcastManager(): BroadcastManager
    {
        return assertInstance(BroadcastFacade::getFacadeRoot(), BroadcastManager::class);
    }

    /**
     * Resolve queueing dispatcher.
     */
    public static function resolveQueueingDispatcher(): QueueingDispatcherContract
    {
        return assertInstance(BusFacade::getFacadeRoot(), QueueingDispatcherContract::class);
    }

    /**
     * Resolve cache manager.
     */
    public static function resolveCacheManager(): CacheManager
    {
        return assertInstance(CacheFacade::getFacadeRoot(), CacheManager::class);
    }

    /**
     * Resolve config repository.
     */
    public static function resolveConfigRepository(): ConfigRepository
    {
        return assertInstance(ConfigFacade::getFacadeRoot(), ConfigRepository::class);
    }

    /**
     * Resolve cookie jar.
     */
    public static function resolveCookieJar(): CookieJar
    {
        return assertInstance(CookieFacade::getFacadeRoot(), CookieJar::class);
    }

    /**
     * Resolve encrypter.
     */
    public static function resolveEncrypter(): Encrypter
    {
        return assertInstance(CryptFacade::getFacadeRoot(), Encrypter::class);
    }

    /**
     * Resolve date factory.
     */
    public static function resolveDateFactory(): DateFactory
    {
        return assertInstance(DateFacade::getFacadeRoot(), DateFactory::class);
    }

    /**
     * Resolve database manager.
     */
    public static function resolveDatabaseManager(): DatabaseManager
    {
        return assertInstance(DBFacade::getFacadeRoot(), DatabaseManager::class);
    }

    /**
     * Resolve event dispatcher.
     */
    public static function resolveEventDispatcher(): EventDispatcherContract
    {
        return assertInstance(EventFacade::getFacadeRoot(), EventDispatcherContract::class);
    }

    /**
     * Resolve filesystem.
     */
    public static function resolveFilesystem(): Filesystem
    {
        return assertInstance(FileFacade::getFacadeRoot(), Filesystem::class);
    }

    /**
     * Resolve gate.
     */
    public static function resolveGate(): Gate
    {
        return assertInstance(GateFacade::getFacadeRoot(), Gate::class);
    }

    /**
     * Resolve hash manager.
     */
    public static function resolveHashManager(): HashManager
    {
        return assertInstance(HashFacade::getFacadeRoot(), HashManager::class);
    }

    /**
     * Resolve http client factory.
     */
    public static function resolveHttpClientFactory(): HttpClientFactory
    {
        return assertInstance(HttpFacade::getFacadeRoot(), HttpClientFactory::class);
    }

    /**
     * Resolve translator.
     */
    public static function resolveTranslator(): Translator
    {
        return assertInstance(LangFacade::getFacadeRoot(), Translator::class);
    }

    /**
     * Resolve log manager.
     */
    public static function resolveLogManager(): LogManager
    {
        return assertInstance(LogFacade::getFacadeRoot(), LogManager::class);
    }

    /**
     * Resolve mail factory.
     */
    public static function resolveMailFactory(): MailFactoryContract
    {
        return assertInstance(MailFacade::getFacadeRoot(), MailFactoryContract::class);
    }

    /**
     * Resolve notification factory.
     */
    public static function resolveNotificationFactory(): NotificationFactoryContract
    {
        return assertInstance(NotificationFacade::getFacadeRoot(), NotificationFactoryContract::class);
    }

    /**
     * Resolve parallel testing.
     */
    public static function resolveParallelTesting(): ParallelTesting
    {
        return assertInstance(ParallelTestingFacade::getFacadeRoot(), ParallelTesting::class);
    }

    /**
     * Resolve password broker manager.
     */
    public static function resolvePasswordBrokerManager(): PasswordBrokerManager
    {
        return assertInstance(PasswordFacade::getFacadeRoot(), PasswordBrokerManager::class);
    }

    /**
     * Resolve queue manager.
     */
    public static function resolveQueueManager(): QueueManager
    {
        return assertInstance(QueueFacade::getFacadeRoot(), QueueManager::class);
    }

    /**
     * Resolve rate limiter.
     */
    public static function resolveRateLimiter(): RateLimiter
    {
        return assertInstance(RateLimiterFacade::getFacadeRoot(), RateLimiter::class);
    }

    /**
     * Resolve redirector.
     */
    public static function resolveRedirector(): Redirector
    {
        return assertInstance(RedirectFacade::getFacadeRoot(), Redirector::class);
    }

    /**
     * Resolve redis manager.
     */
    public static function resolveRedisManager(): RedisManager
    {
        return assertInstance(RedisFacade::getFacadeRoot(), RedisManager::class);
    }

    /**
     * Resolve request.
     */
    public static function resolveRequest(): Request
    {
        return assertInstance(RequestFacade::getFacadeRoot(), Request::class);
    }

    /**
     * Resolve response factory.
     */
    public static function resolveResponseFactory(): ResponseFactory
    {
        return assertInstance(ResponseFacade::getFacadeRoot(), ResponseFactory::class);
    }

    /**
     * Resolve router.
     */
    public static function resolveRouter(): Router
    {
        return assertInstance(RouteFacade::getFacadeRoot(), Router::class);
    }

    /**
     * Resolve schema builder.
     */
    public static function resolveSchemaBuilder(): SchemaBuilder
    {
        return assertInstance(SchemaFacade::getFacadeRoot(), SchemaBuilder::class);
    }

    /**
     * Resolve session manager.
     */
    public static function resolveSessionManager(): SessionManager
    {
        return assertInstance(SessionFacade::getFacadeRoot(), SessionManager::class);
    }

    /**
     * Resolve filesystem manager.
     */
    public static function resolveFilesystemManager(): FilesystemManager
    {
        return assertInstance(StorageFacade::getFacadeRoot(), FilesystemManager::class);
    }

    /**
     * Resolve url generator.
     */
    public static function resolveUrlGenerator(): UrlGenerator
    {
        return assertInstance(URLFacade::getFacadeRoot(), UrlGenerator::class);
    }

    /**
     * Resolve validator factory.
     */
    public static function resolveValidatorFactory(): ValidationFactory
    {
        return assertInstance(ValidatorFacade::getFacadeRoot(), ValidationFactory::class);
    }

    /**
     * Resolve view factory.
     */
    public static function resolveViewFactory(): ViewFactory
    {
        return assertInstance(ViewFacade::getFacadeRoot(), ViewFactory::class);
    }

    /**
     * Resolve vite.
     */
    public static function resolveVite(): Vite
    {
        return static::resolve(Vite::class);
    }

    /**
     * Resolve mix.
     */
    public static function resolveMix(): Mix
    {
        return static::resolve(Mix::class);
    }

    /**
     * Resolve exception handler.
     */
    public static function resolveExceptionHandler(): ExceptionHandler
    {
        return static::make(ExceptionHandlerContract::class, ExceptionHandler::class);
    }

    /**
     * Make new service from the container.
     *
     * @template T of object
     *
     * @param class-string<T> $class
     * @param array<mixed> $parameters
     *
     * @return T
     */
    public static function make(string $abstract, string $class, array $parameters = []): object
    {
        return assertInstance(static::resolveApp()->make($abstract, $parameters), $class);
    }

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
    public static function resolve(string $class, array $parameters = []): object
    {
        return assertInstance(static::resolveApp()->make($class, $parameters), $class);
    }

    /**
     * Resolve http kernel.
     */
    public static function resolveHttpKernel(): HttpKernel
    {
        return static::make(HttpKernelContract::class, HttpKernel::class);
    }

    /**
     * Resolve session store.
     */
    public static function resolveSessionStore(?string $driver = null): SessionStore
    {
        return assertInstance(static::resolveSessionManager()->driver($driver), SessionStore::class);
    }

    /**
     * Resolve route registrar.
     */
    public static function resolveRouteRegistrar(): RouteRegistrar
    {
        return new RouteRegistrar(static::resolveRouter());
    }

    /**
     * Resolve hasher.
     */
    public static function resolveHasher(?string $driver = null): HasherContract
    {
        return assertInstance(static::resolveHashManager()->driver($driver), HasherContract::class);
    }

    /**
     * Resolve database token guard.
     */
    public static function resolveDatabaseTokenGuard(?string $name = null): DatabaseTokenGuard
    {
        return assertInstance(static::resolveAuthManager()->guard($name), DatabaseTokenGuard::class);
    }

    /**
     * Resolve eloquent user provider.
     */
    public static function resolveEloquentUserProvider(?string $name = null): EloquentUserProvider
    {
        return assertInstance(static::resolveAuthManager()->createUserProvider($name), EloquentUserProvider::class);
    }

    /**
     * Resolve password broker.
     */
    public static function resolvePasswordBroker(?string $name = null): PasswordBroker
    {
        return assertInstance(static::resolvePasswordBrokerManager()->broker($name), PasswordBroker::class);
    }

    /**
     * Resolve database token repository.
     */
    public static function resolveDatabaseTokenRepository(?string $name = null): DatabaseTokenRepository
    {
        return assertInstance(static::resolvePasswordBroker($name)->getRepository(), DatabaseTokenRepository::class);
    }

    /**
     * Resolve database presence verifier.
     */
    public static function resolvePresenceVerifier(): PresenceVerifierInterface
    {
        return static::make('validation.presence', PresenceVerifierInterface::class);
    }

    /**
     * Resolve current route.
     */
    public static function resolveRoute(): Route
    {
        return assertInstance(static::resolveRouter()->current(), Route::class);
    }

    /**
     * Resolve validator.
     *
     * @param array<mixed> $data
     * @param array<mixed> $rules
     * @param array<mixed> $messages
     * @param array<mixed> $attributes
     */
    public static function resolveValidator(array $data = [], array $rules = [], array $messages = [], array $attributes = []): Validator
    {
        $request = static::resolveRequest();
        $factory = static::resolveValidatorFactory();

        if ($request->expectsJson() || $request->getRequestFormat() === 'json') {
            $factory = new SecureValidationFactory($factory);
        }

        return $factory->make($data, $rules, $messages, $attributes);
    }
}

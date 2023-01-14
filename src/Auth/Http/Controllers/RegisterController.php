<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Http\Controllers;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\UserProvider as UserProviderContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Tomchochola\Laratchi\Auth\Actions\CanLoginAction;
use Tomchochola\Laratchi\Auth\Actions\LoginAction;
use Tomchochola\Laratchi\Auth\Http\Requests\RegisterRequest;
use Tomchochola\Laratchi\Auth\Services\AuthService;
use Tomchochola\Laratchi\Providers\LaratchiServiceProvider;
use Tomchochola\Laratchi\Routing\TransactionController;

class RegisterController extends TransactionController
{
    /**
     * Throttle max attempts.
     */
    public static int $throttle = 5;

    /**
     * Throttle decay in minutes.
     */
    public static int $decay = 15;

    /**
     * Login user after register.
     */
    public static bool $loginAfterRegister = true;

    /**
     * Throw simple throttle errors.
     */
    public static bool $simpleThrottle = false;

    /**
     * Handle the incoming request.
     */
    public function __invoke(RegisterRequest $request): SymfonyResponse
    {
        $this->validateUnique($request);

        $response = $this->beforeCreating($request);

        if ($response !== null) {
            return $response;
        }

        $user = $this->createUser($request);

        $this->fireRegisteredEvent($request, $user);

        if ($this->loginAfterRegister() === false) {
            return resolveResponseFactory()->noContent();
        }

        $this->ensureCanLogin($request, $user);

        $this->login($request, $user);

        return $this->response($request, $user);
    }

    /**
     * Throttle limit.
     */
    protected function limit(RegisterRequest $request, string $key): Limit
    {
        return Limit::perMinutes(static::$decay, static::$throttle)->by(requestSignature()->data('key', $key)->hash());
    }

    /**
     * Throttle callback.
     *
     * @param array<string, mixed> $credentials
     *
     * @return (Closure(int): never)|null
     */
    protected function onThrottle(RegisterRequest $request, array $credentials): ?Closure
    {
        return static function (int $seconds) use ($credentials, $request): never {
            if (static::$simpleThrottle) {
                throw new ThrottleRequestsException();
            }

            $request->throwThrottleValidationError(\array_keys($credentials), $seconds);
        };
    }

    /**
     * Retrieve by credentials.
     *
     * @param array<string, mixed> $credentials
     */
    protected function retrieveByCredentials(RegisterRequest $request, array $credentials): ?AuthenticatableContract
    {
        return $this->userProvider($request)->retrieveByCredentials($credentials);
    }

    /**
     * Get user provider.
     */
    protected function userProvider(RegisterRequest $request): UserProviderContract
    {
        return inject(AuthService::class)->userProvider(resolveAuthManager()->guard($request->guardName()));
    }

    /**
     * Login.
     */
    protected function login(RegisterRequest $request, AuthenticatableContract $user): void
    {
        inject(LoginAction::class)->handle($request->guardName(), $user, false);
    }

    /**
     * Make response.
     */
    protected function response(RegisterRequest $request, AuthenticatableContract $user): SymfonyResponse
    {
        $user = $this->modifyUser($request, $user);

        return (new LaratchiServiceProvider::$meJsonApiResource($user))->toResponse($request);
    }

    /**
     * Modify user before response.
     */
    protected function modifyUser(RegisterRequest $request, AuthenticatableContract $user): AuthenticatableContract
    {
        return inject(AuthService::class)->modifyUser($user);
    }

    /**
     * Throw duplicate credentials error.
     *
     * @param array<string, mixed> $credentials
     */
    protected function throwDuplicateCredentialsError(RegisterRequest $request, array $credentials): never
    {
        $request->throwUniqueValidationException(\array_keys($credentials));
    }

    /**
     * Create new user.
     */
    protected function createUser(RegisterRequest $request): AuthenticatableContract
    {
        $userProvider = $this->userProvider($request);

        \assert($userProvider instanceof EloquentUserProvider);

        $user = $userProvider->createModel();

        \assert($user instanceof AuthenticatableContract);

        $password = $request->password()['password'];

        \assert(\is_string($password));

        $user->forceFill($request->data());
        $user->forceFill(['password' => resolveHasher()->make($password)]);

        $this->makeChanges($request, $user);

        $ok = $user->save();

        \assert($ok);

        return $user->refresh();
    }

    /**
     * Fire registered event.
     */
    protected function fireRegisteredEvent(RegisterRequest $request, AuthenticatableContract $user): void
    {
        resolveEventDispatcher()->dispatch(new Registered($user));
    }

    /**
     * Before creating shortcut.
     */
    protected function beforeCreating(RegisterRequest $request): ?SymfonyResponse
    {
        return null;
    }

    /**
     * Validate given credentials are unique.
     */
    protected function validateUnique(RegisterRequest $request): void
    {
        $credentialsArray = $request->credentials();

        foreach ($credentialsArray as $index => $credentials) {
            [$hit] = $this->throttle($this->limit($request, "credentials.{$index}"), $this->onThrottle($request, $credentials));

            $user = $this->retrieveByCredentials($request, $credentials);

            if ($user !== null) {
                $hit();

                $this->throwDuplicateCredentialsError($request, $credentials);
            }
        }
    }

    /**
     * Check if user can login.
     */
    protected function ensureCanLogin(RegisterRequest $request, AuthenticatableContract $user): void
    {
        $response = inject(CanLoginAction::class)->authorize($user);

        if ($response->denied()) {
            $this->throwCanNotLoginError($request, $user, $response);
        }
    }

    /**
     * Throw can not login error.
     */
    protected function throwCanNotLoginError(RegisterRequest $request, AuthenticatableContract $user, Response $response): never
    {
        $message = $response->message();

        if ($message === null || \trim($message) === '') {
            $message = 'auth.blocked';
        }

        if ($response->code() === null) {
            $keys = [];

            foreach ($request->credentials() as $credentials) {
                $keys = \array_merge($keys, $credentials);
            }

            $request->throwSingleValidationException(\array_keys($keys), $message, $response->status());
        }

        throw (new AuthorizationException($message, $response->code()))
            ->setResponse($response)
            ->withStatus($response->status());
    }

    /**
     * If should login after register.
     */
    protected function loginAfterRegister(): bool
    {
        return static::$loginAfterRegister;
    }

    /**
     * Make changes.
     */
    protected function makeChanges(RegisterRequest $request, Model&AuthenticatableContract $user): void
    {
    }
}

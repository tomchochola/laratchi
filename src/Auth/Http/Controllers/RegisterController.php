<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Http\Controllers;

use Closure;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\UserProvider as UserProviderContract;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Tomchochola\Laratchi\Auth\Actions\LoginAction;
use Tomchochola\Laratchi\Auth\Http\Requests\RegisterRequest;
use Tomchochola\Laratchi\Auth\Services\AuthService;
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
        return function (int $seconds) use ($credentials): never {
            $this->throwThrottleValidationError(\array_keys($credentials), $seconds);
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
        return inject(AuthService::class)->jsonApiResource($user)->toResponse($request);
    }

    /**
     * Throw duplicate credentials error.
     *
     * @param array<string, mixed> $credentials
     */
    protected function throwDuplicateCredentialsError(RegisterRequest $request, array $credentials): void
    {
        $validator = resolveValidatorFactory()->make([], []);

        foreach ($credentials as $key => $value) {
            $validator->addFailure($key, 'unique');
        }

        throw new ValidationException($validator);
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
}

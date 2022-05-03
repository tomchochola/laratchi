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
        [$hit] = $this->throttle($this->limit($request, 'credentials'), $this->onThrottle($request));

        $user = $this->retrieveByCredentials($request);

        if ($user !== null) {
            $hit();

            $this->throwDuplicateCredentialsError($request);
        }

        $response = $this->beforeCreating($request, $hit);

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
     * @return (Closure(int): never)|null
     */
    protected function onThrottle(RegisterRequest $request): ?Closure
    {
        return function (int $seconds) use ($request): never {
            $this->throwThrottleValidationError(\array_keys($request->credentials()), $seconds);
        };
    }

    /**
     * Retrieve by credentials.
     */
    protected function retrieveByCredentials(RegisterRequest $request): ?AuthenticatableContract
    {
        return $this->userProvider($request)->retrieveByCredentials($request->credentials());
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
        inject(LoginAction::class)->handle($request->guardName(), $user, $request->remember());
    }

    /**
     * Make response.
     */
    protected function response(RegisterRequest $request, AuthenticatableContract $user): SymfonyResponse
    {
        return inject(AuthService::class)->jsonApiResource($user)->toResponse($request);
    }

    /**
     * Validate request.
     */
    protected function validate(RegisterRequest $request): void
    {
    }

    /**
     * Throw duplicate credentials error.
     */
    protected function throwDuplicateCredentialsError(RegisterRequest $request): void
    {
        $validator = resolveValidatorFactory()->make([], []);

        foreach ($request->credentials() as $key => $value) {
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
        $user->forceFill(['password' => resolveHasher()->make($password), 'locale' => $request->locale()]);

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
    protected function beforeCreating(RegisterRequest $request, Closure $hit): ?SymfonyResponse
    {
        return null;
    }
}

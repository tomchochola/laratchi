<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Http\Controllers;

use Closure;
use Illuminate\Auth\Events\Attempting;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Validated;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\UserProvider as UserProviderContract;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Tomchochola\Laratchi\Auth\Actions\LoginAction;
use Tomchochola\Laratchi\Auth\Http\Requests\LoginRequest;
use Tomchochola\Laratchi\Auth\Services\AuthService;
use Tomchochola\Laratchi\Routing\TransactionController;

class LoginController extends TransactionController
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
    public function __invoke(LoginRequest $request): SymfonyResponse
    {
        [$hitCredentials] = $this->throttle($this->limit($request, 'credentials'), $this->onCredentialsThrottle($request));
        [$hitPassword] = $this->throttle($this->limit($request, 'password'), $this->onPasswordThrottle($request));

        $this->fireAttemptingEvent($request);

        $user = $this->retrieveByCredentials($request);

        if ($user === null) {
            $hitCredentials();

            $this->fireRetrieveByCredentialsFailedEvent($request, $user);
            $this->throwRetrieveByCredentialsFailedError($request, $user);
        }

        $ok = $this->validatePassword($request, $user);

        if (! $ok) {
            $hitPassword();

            $this->fireValidatePasswordFailedEvent($request, $user);
            $this->throwValidatePasswordFailedError($request, $user);
        }

        $this->fireValidatedEvent($request, $user);

        $this->login($request, $user);

        return $this->response($request, $user);
    }

    /**
     * Throttle limit.
     */
    protected function limit(LoginRequest $request, string $key): Limit
    {
        return Limit::perMinutes(static::$decay, static::$throttle)->by(requestSignature()->data('key', $key)->hash());
    }

    /**
     * Throttle credentials callback.
     *
     * @return (Closure(int): never)|null
     */
    protected function onCredentialsThrottle(LoginRequest $request): ?Closure
    {
        return function (int $seconds) use ($request): never {
            $this->throwThrottleValidationError(\array_keys($request->credentials()), $seconds, 'auth.throttle');
        };
    }

    /**
     * Throttle password callback.
     *
     * @return (Closure(int): never)|null
     */
    protected function onPasswordThrottle(LoginRequest $request): ?Closure
    {
        return function (int $seconds) use ($request): never {
            resolveEventDispatcher()->dispatch(new Lockout($request));

            $this->throwThrottleValidationError(\array_keys($request->password()), $seconds, 'auth.throttle');
        };
    }

    /**
     * Fire attempting event.
     */
    protected function fireAttemptingEvent(LoginRequest $request): void
    {
        resolveEventDispatcher()->dispatch(new Attempting($request->guardName(), $request->credentials(), $request->remember()));
    }

    /**
     * Retrieve by credentials.
     */
    protected function retrieveByCredentials(LoginRequest $request): ?AuthenticatableContract
    {
        return $this->userProvider($request)->retrieveByCredentials($request->credentials());
    }

    /**
     * Get user provider.
     */
    protected function userProvider(LoginRequest $request): UserProviderContract
    {
        return inject(AuthService::class)->userProvider(resolveAuthManager()->guard($request->guardName()));
    }

    /**
     * Fire retrieve by credentials failed event.
     */
    protected function fireRetrieveByCredentialsFailedEvent(LoginRequest $request, ?AuthenticatableContract $user): void
    {
        resolveEventDispatcher()->dispatch(new Failed($request->guardName(), $user, $request->credentials()));
    }

    /**
     * Throw retrieve by credentials failed error.
     */
    protected function throwRetrieveByCredentialsFailedError(LoginRequest $request, ?AuthenticatableContract $user): never
    {
        throw ValidationException::withMessages(\array_map(static fn (): array => [mustTransString('auth.failed')], $request->credentials()));
    }

    /**
     * Validate password.
     */
    protected function validatePassword(LoginRequest $request, AuthenticatableContract $user): bool
    {
        return $this->userProvider($request)->validateCredentials($user, $request->password());
    }

    /**
     * Fire validate password failed event.
     */
    protected function fireValidatePasswordFailedEvent(LoginRequest $request, ?AuthenticatableContract $user): void
    {
        resolveEventDispatcher()->dispatch(new Failed($request->guardName(), $user, $request->password()));
    }

    /**
     * Throw validate password failed error.
     */
    protected function throwValidatePasswordFailedError(LoginRequest $request, ?AuthenticatableContract $user): never
    {
        throw ValidationException::withMessages(\array_map(static fn (): array => [mustTransString('auth.password')], $request->password()));
    }

    /**
     * Fire validated event.
     */
    protected function fireValidatedEvent(LoginRequest $request, AuthenticatableContract $user): void
    {
        resolveEventDispatcher()->dispatch(new Validated($request->guardName(), $user));
    }

    /**
     * Login.
     */
    protected function login(LoginRequest $request, AuthenticatableContract $user): void
    {
        inject(LoginAction::class)->handle($request->guardName(), $user, $request->remember());
    }

    /**
     * Make response.
     */
    protected function response(LoginRequest $request, AuthenticatableContract $user): SymfonyResponse
    {
        return inject(AuthService::class)->jsonApiResource($user)->toResponse($request);
    }
}

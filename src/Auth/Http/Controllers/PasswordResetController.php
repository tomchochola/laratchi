<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Http\Controllers;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Contracts\Auth\PasswordBroker as PasswordBrokerContract;
use Illuminate\Contracts\Auth\UserProvider as UserProviderContract;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Tomchochola\Laratchi\Auth\Actions\CanLoginAction;
use Tomchochola\Laratchi\Auth\Actions\CycleRememberTokenAction;
use Tomchochola\Laratchi\Auth\Actions\LoginAction;
use Tomchochola\Laratchi\Auth\Actions\LogoutOtherDevicesAction;
use Tomchochola\Laratchi\Auth\Actions\UpdatePasswordAction;
use Tomchochola\Laratchi\Auth\Http\Requests\PasswordResetRequest;
use Tomchochola\Laratchi\Auth\Services\AuthService;
use Tomchochola\Laratchi\Providers\LaratchiServiceProvider;
use Tomchochola\Laratchi\Routing\TransactionController;

class PasswordResetController extends TransactionController
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
     * Login user after reset.
     */
    public static bool $loginAfterReset = true;

    /**
     * Throw simple throttle errors.
     */
    public static bool $simpleThrottle = false;

    /**
     * Resetted password user.
     */
    protected AuthenticatableContract&CanResetPasswordContract $user;

    /**
     * Handle the incoming request.
     */
    public function __invoke(PasswordResetRequest $request): SymfonyResponse
    {
        [$hit] = $this->throttle($this->limit($request, 'status'), $this->onThrottle($request));

        $response = $this->beforeResetting($request);

        if ($response !== null) {
            return $response;
        }

        $status = $this->resetPassword($request);

        if ($status !== PasswordBrokerContract::PASSWORD_RESET) {
            $hit();

            $this->throwInvalidStatus($request, $status);
        }

        if ($this->loginAfterReset() === false) {
            return resolveResponseFactory()->noContent();
        }

        $this->ensureCanLogin($request);

        $this->login($request);

        return $this->response($request);
    }

    /**
     * Throttle limit.
     */
    protected function limit(PasswordResetRequest $request, string $key): Limit
    {
        return Limit::perMinutes(static::$decay, static::$throttle)->by(requestSignature()->data('key', $key)->hash());
    }

    /**
     * Throttle callback.
     *
     * @return (Closure(int): never)|null
     */
    protected function onThrottle(PasswordResetRequest $request): ?Closure
    {
        return static function (int $seconds) use ($request): never {
            if (static::$simpleThrottle) {
                throw new ThrottleRequestsException();
            }

            $request->throwThrottleValidationError(\array_keys($request->credentials()), $seconds);
        };
    }

    /**
     * Reset password.
     */
    protected function resetPassword(PasswordResetRequest $request): string
    {
        $status = $this->passwordBroker($request)->reset($request->credentials(), function (AuthenticatableContract&CanResetPasswordContract $user, string $password) use ($request): void {
            $this->user = $user;

            $this->updatePassword($request, $password);

            $this->cycleRememberToken($request);

            $this->logoutOtherDevices($request);

            $this->firePasswordResetEvent($request);
        });

        if (! \is_string($status)) {
            return PasswordBrokerContract::INVALID_USER;
        }

        return $status;
    }

    /**
     * Get password broker.
     */
    protected function passwordBroker(PasswordResetRequest $request): PasswordBrokerContract
    {
        return resolvePasswordBrokerManager()->broker($request->passwordBrokerName());
    }

    /**
     * Throw invalid status error.
     */
    protected function throwInvalidStatus(PasswordResetRequest $request, string $status): never
    {
        $request->throwSingleValidationException(\array_keys($request->credentials()), $status);
    }

    /**
     * Update password.
     */
    protected function updatePassword(PasswordResetRequest $request, string $password): void
    {
        inject(UpdatePasswordAction::class)->handle($this->user, $password);
    }

    /**
     * Cycle remember token.
     */
    protected function cycleRememberToken(PasswordResetRequest $request): void
    {
        inject(CycleRememberTokenAction::class)->handle($this->user);

        $this->userProvider($request)->updateRememberToken($this->user, $this->user->getRememberToken());
    }

    /**
     * Get user provider.
     */
    protected function userProvider(PasswordResetRequest $request): UserProviderContract
    {
        return inject(AuthService::class)->userProvider(resolveAuthManager()->guard($request->guardName()));
    }

    /**
     * Logout other devices.
     */
    protected function logoutOtherDevices(PasswordResetRequest $request): void
    {
        inject(LogoutOtherDevicesAction::class)->handle($this->user);
    }

    /**
     * Make response.
     */
    protected function response(PasswordResetRequest $request): SymfonyResponse
    {
        $user = $this->modifyUser($request, $this->user);

        return (new LaratchiServiceProvider::$meResource($user))->toResponse($request);
    }

    /**
     * Modify user before response.
     */
    protected function modifyUser(PasswordResetRequest $request, AuthenticatableContract $user): AuthenticatableContract
    {
        return inject(AuthService::class)->modifyUser($user);
    }

    /**
     * Login.
     */
    protected function login(PasswordResetRequest $request): void
    {
        inject(LoginAction::class)->handle($request->guardName(), $this->user, false);
    }

    /**
     * Before resetting shortcut.
     */
    protected function beforeResetting(PasswordResetRequest $request): ?SymfonyResponse
    {
        return null;
    }

    /**
     * Fire password reset event.
     */
    protected function firePasswordResetEvent(PasswordResetRequest $request): void
    {
        resolveEventDispatcher()->dispatch(new PasswordReset($this->user));
    }

    /**
     * Check if user can login.
     */
    protected function ensureCanLogin(PasswordResetRequest $request): void
    {
        $response = inject(CanLoginAction::class)->authorize($this->user);

        if ($response->denied()) {
            $this->throwCanNotLoginError($request, $response);
        }
    }

    /**
     * Throw can not login error.
     */
    protected function throwCanNotLoginError(PasswordResetRequest $request, Response $response): never
    {
        $message = $response->message();

        if ($message === null || \trim($message) === '') {
            $message = 'auth.blocked';
        }

        if ($response->code() === null) {
            $request->throwSingleValidationException(\array_keys($request->credentials()), $message, $response->status());
        }

        throw (new AuthorizationException($message, $response->code()))
            ->setResponse($response)
            ->withStatus($response->status());
    }

    /**
     * If should login after reset.
     */
    protected function loginAfterReset(): bool
    {
        return static::$loginAfterReset;
    }
}

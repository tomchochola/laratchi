<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Http\Controllers;

use Closure;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Contracts\Auth\PasswordBroker as PasswordBrokerContract;
use Illuminate\Contracts\Auth\UserProvider as UserProviderContract;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Tomchochola\Laratchi\Auth\Actions\CycleRememberTokenAction;
use Tomchochola\Laratchi\Auth\Actions\LoginAction;
use Tomchochola\Laratchi\Auth\Actions\LogoutOtherDevicesAction;
use Tomchochola\Laratchi\Auth\Actions\UpdatePasswordAction;
use Tomchochola\Laratchi\Auth\Http\Requests\PasswordResetRequest;
use Tomchochola\Laratchi\Auth\Services\AuthService;
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
     * Resetted password user.
     */
    protected AuthenticatableContract & CanResetPasswordContract $user;

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
        return function (int $seconds) use ($request): never {
            $this->throwThrottleValidationError(\array_keys($request->credentials()), $seconds);
        };
    }

    /**
     * Reset password.
     */
    protected function resetPassword(PasswordResetRequest $request): string
    {
        $status = $this->passwordBroker($request)->reset($request->credentials(), function (AuthenticatableContract & CanResetPasswordContract $user, string $password) use ($request): void {
            $this->user = $user;

            $this->updatePassword($request, $password);

            $this->cycleRememberToken($request);

            $this->logoutOtherDevices($request);
        });

        \assert(\is_string($status));

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
        throw ValidationException::withMessages(\array_map(static fn (): array => [mustTransString($status)], $request->credentials()));
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
        return inject(AuthService::class)->jsonApiResource($this->user)->toResponse($request);
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
}

<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Http\Controllers;

use Closure;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\OtherDeviceLogout;
use Illuminate\Auth\Events\Validated;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Auth\UserProvider as UserProviderContract;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Tomchochola\Laratchi\Auth\Actions\CycleRememberTokenAction;
use Tomchochola\Laratchi\Auth\Actions\LogoutOtherDevicesAction;
use Tomchochola\Laratchi\Auth\Actions\ReloginAction;
use Tomchochola\Laratchi\Auth\Actions\UpdatePasswordAction;
use Tomchochola\Laratchi\Auth\Http\Requests\LogoutOtherDevicesRequest;
use Tomchochola\Laratchi\Auth\Services\AuthService;
use Tomchochola\Laratchi\Providers\LaratchiServiceProvider;
use Tomchochola\Laratchi\Routing\TransactionController;

class LogoutOtherDevicesController extends TransactionController
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
     * Throw simple throttle errors.
     */
    public static bool $simpleThrottle = false;

    /**
     * Handle the incoming request.
     */
    public function __invoke(LogoutOtherDevicesRequest $request): SymfonyResponse
    {
        [$hit] = $this->throttle($this->limit($request, 'password'), $this->onThrottle($request));

        $ok = $this->validatePassword($request);

        if (! $ok) {
            $hit();

            $this->fireValidatePasswordFailedEvent($request);
            $this->throwValidatePasswordFailedError($request);
        }

        $this->fireValidatedEvent($request);

        $response = $this->beforeUpdatingPassword($request);

        if ($response !== null) {
            return $response;
        }

        $this->updatePassword($request);

        $this->cycleRememberToken($request);

        $this->logoutOtherDevices($request);

        $this->fireOtherDeviceLogoutEvent($request);

        $this->relogin($request);

        return $this->response($request);
    }

    /**
     * Throttle limit.
     */
    protected function limit(LogoutOtherDevicesRequest $request, string $key): Limit
    {
        return Limit::perMinutes(static::$decay, static::$throttle)->by(requestSignature()->data('key', $key)->user($request->retrieveUser())->hash());
    }

    /**
     * Throttle callback.
     *
     * @return (Closure(int): never)|null
     */
    protected function onThrottle(LogoutOtherDevicesRequest $request): ?Closure
    {
        return static function (int $seconds) use ($request): never {
            resolveEventDispatcher()->dispatch(new Lockout($request));

            if (static::$simpleThrottle) {
                throw new ThrottleRequestsException();
            }

            $request->throwThrottleValidationError(\array_keys($request->password()), $seconds);
        };
    }

    /**
     * Validate password.
     */
    protected function validatePassword(LogoutOtherDevicesRequest $request): bool
    {
        if (\count($request->password()) === 0) {
            return true;
        }

        return $this->userProvider($request)->validateCredentials($request->retrieveUser(), $request->password());
    }

    /**
     * Fire validate password failed event.
     */
    protected function fireValidatePasswordFailedEvent(LogoutOtherDevicesRequest $request): void
    {
        resolveEventDispatcher()->dispatch(new Failed($request->guardName(), $request->retrieveUser(), $request->password()));
    }

    /**
     * Throw validate password failed error.
     */
    protected function throwValidatePasswordFailedError(LogoutOtherDevicesRequest $request): never
    {
        $request->throwValidationException(\array_map(static fn (): array => ['auth.password' => []], $request->password()));
    }

    /**
     * Update password.
     */
    protected function updatePassword(LogoutOtherDevicesRequest $request): void
    {
        $password = $request->password()['password'] ?? null;

        if ($password === null) {
            return;
        }

        \assert(\is_string($password));

        inject(UpdatePasswordAction::class)->handle($request->retrieveUser(), $password, false);
    }

    /**
     * Get user provider.
     */
    protected function userProvider(LogoutOtherDevicesRequest $request): UserProviderContract
    {
        return inject(AuthService::class)->userProvider(resolveAuthManager()->guard($request->guardName()));
    }

    /**
     * Cycle remember token.
     */
    protected function cycleRememberToken(LogoutOtherDevicesRequest $request): void
    {
        $user = $request->retrieveUser();

        inject(CycleRememberTokenAction::class)->handle($user);

        $this->userProvider($request)->updateRememberToken($user, $user->getRememberToken());
    }

    /**
     * Logout other devices.
     */
    protected function logoutOtherDevices(LogoutOtherDevicesRequest $request): void
    {
        inject(LogoutOtherDevicesAction::class)->handle($request->retrieveUser());
    }

    /**
     * Fire other device logout event.
     */
    protected function fireOtherDeviceLogoutEvent(LogoutOtherDevicesRequest $request): void
    {
        resolveEventDispatcher()->dispatch(new OtherDeviceLogout($request->guardName(), $request->retrieveUser()));
    }

    /**
     * Relogin.
     */
    protected function relogin(LogoutOtherDevicesRequest $request): void
    {
        inject(ReloginAction::class)->handle($request->guardName());
    }

    /**
     * Make response.
     */
    protected function response(LogoutOtherDevicesRequest $request): SymfonyResponse
    {
        return (new LaratchiServiceProvider::$meJsonApiResource($request->retrieveUser()))->toResponse($request);
    }

    /**
     * Fire validated event.
     */
    protected function fireValidatedEvent(LogoutOtherDevicesRequest $request): void
    {
        resolveEventDispatcher()->dispatch(new Validated($request->guardName(), $request->retrieveUser()));
    }

    /**
     * Before updating password shortcut.
     */
    protected function beforeUpdatingPassword(LogoutOtherDevicesRequest $request): ?SymfonyResponse
    {
        return null;
    }
}

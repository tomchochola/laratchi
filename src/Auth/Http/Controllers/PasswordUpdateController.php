<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Http\Controllers;

use Closure;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Validated;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Auth\UserProvider as UserProviderContract;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Tomchochola\Laratchi\Auth\Actions\CycleRememberTokenAction;
use Tomchochola\Laratchi\Auth\Actions\LogoutOtherDevicesAction;
use Tomchochola\Laratchi\Auth\Actions\ReloginAction;
use Tomchochola\Laratchi\Auth\Actions\UpdatePasswordAction;
use Tomchochola\Laratchi\Auth\Events\PasswordUpdateEvent;
use Tomchochola\Laratchi\Auth\Http\Requests\PasswordUpdateRequest;
use Tomchochola\Laratchi\Auth\Services\AuthService;
use Tomchochola\Laratchi\Providers\LaratchiServiceProvider;
use Tomchochola\Laratchi\Routing\TransactionController;

class PasswordUpdateController extends TransactionController
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
    public function __invoke(PasswordUpdateRequest $request): SymfonyResponse
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

        $this->firePasswordUpdateEvent($request);

        $this->relogin($request);

        return $this->response($request);
    }

    /**
     * Throttle limit.
     */
    protected function limit(PasswordUpdateRequest $request, string $key): Limit
    {
        return Limit::perMinutes(static::$decay, static::$throttle)->by(requestSignature()->data('key', $key)->user($request->retrieveUser())->hash());
    }

    /**
     * Throttle callback.
     *
     * @return (Closure(int): never)|null
     */
    protected function onThrottle(PasswordUpdateRequest $request): ?Closure
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
     * Update password.
     */
    protected function updatePassword(PasswordUpdateRequest $request): void
    {
        inject(UpdatePasswordAction::class)->handle($request->retrieveUser(), $request->newPassword());
    }

    /**
     * Logout other devices.
     */
    protected function logoutOtherDevices(PasswordUpdateRequest $request): void
    {
        inject(LogoutOtherDevicesAction::class)->handle($request->retrieveUser());
    }

    /**
     * Make response.
     */
    protected function response(PasswordUpdateRequest $request): SymfonyResponse
    {
        return (new LaratchiServiceProvider::$meJsonApiResource($request->retrieveUser()))->toResponse($request);
    }

    /**
     * Get user provider.
     */
    protected function userProvider(PasswordUpdateRequest $request): UserProviderContract
    {
        return inject(AuthService::class)->userProvider(resolveAuthManager()->guard($request->guardName()));
    }

    /**
     * Validate password.
     */
    protected function validatePassword(PasswordUpdateRequest $request): bool
    {
        return $this->userProvider($request)->validateCredentials($request->retrieveUser(), $request->password());
    }

    /**
     * Fire validate password failed event.
     */
    protected function fireValidatePasswordFailedEvent(PasswordUpdateRequest $request): void
    {
        resolveEventDispatcher()->dispatch(new Failed($request->guardName(), $request->retrieveUser(), $request->password()));
    }

    /**
     * Throw validate password failed error.
     */
    protected function throwValidatePasswordFailedError(PasswordUpdateRequest $request): never
    {
        $request->throwSingleValidationException(\array_keys($request->password()), 'auth.password');
    }

    /**
     * Fire validated event.
     */
    protected function fireValidatedEvent(PasswordUpdateRequest $request): void
    {
        resolveEventDispatcher()->dispatch(new Validated($request->guardName(), $request->retrieveUser()));
    }

    /**
     * Cycle remember token.
     */
    protected function cycleRememberToken(PasswordUpdateRequest $request): void
    {
        $user = $request->retrieveUser();

        inject(CycleRememberTokenAction::class)->handle($user);

        $this->userProvider($request)->updateRememberToken($user, $user->getRememberToken());
    }

    /**
     * Relogin.
     */
    protected function relogin(PasswordUpdateRequest $request): void
    {
        inject(ReloginAction::class)->handle($request->guardName());
    }

    /**
     * Before updating password shortcut.
     */
    protected function beforeUpdatingPassword(PasswordUpdateRequest $request): ?SymfonyResponse
    {
        return null;
    }

    /**
     * Fire password update event.
     */
    protected function firePasswordUpdateEvent(PasswordUpdateRequest $request): void
    {
        resolveEventDispatcher()->dispatch(new PasswordUpdateEvent($request->retrieveUser()));
    }
}

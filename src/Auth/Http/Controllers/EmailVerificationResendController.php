<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Http\Controllers;

use Closure;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Contracts\Auth\UserProvider as UserProviderContract;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tomchochola\Laratchi\Auth\Http\Requests\EmailVerificationResendRequest;
use Tomchochola\Laratchi\Auth\Services\AuthService;
use Tomchochola\Laratchi\Routing\TransactionController;

class EmailVerificationResendController extends TransactionController
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
    public function __invoke(EmailVerificationResendRequest $request): SymfonyResponse
    {
        $this->hit($this->limit($request, ''), $this->onThrottle($request));

        $user = $this->retrieveUser($request);

        $response = $this->beforeSending($request, $user);

        if ($response !== null) {
            return $response;
        }

        $this->sendEmailVerificationNotification($request, $user);

        return $this->response($request, $user);
    }

    /**
     * Throttle limit.
     */
    protected function limit(EmailVerificationResendRequest $request, string $key): Limit
    {
        return Limit::perMinutes(static::$decay, static::$throttle)->by(requestSignature()->data('key', $key)->hash());
    }

    /**
     * Throttle callback.
     *
     * @return (Closure(int): never)|null
     */
    protected function onThrottle(EmailVerificationResendRequest $request): ?Closure
    {
        return function (int $seconds) use ($request): never {
            if (static::$simpleThrottle) {
                throw new ThrottleRequestsException();
            }

            if (\count($request->credentials()) > 0) {
                $this->throwThrottleValidationError(\array_keys($request->credentials()), $seconds);
            }

            throw new ThrottleRequestsException();
        };
    }

    /**
     * Send email verification notification.
     */
    protected function sendEmailVerificationNotification(EmailVerificationResendRequest $request, AuthenticatableContract&MustVerifyEmailContract $user): void
    {
        if (! $user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }
    }

    /**
     * Make response.
     */
    protected function response(EmailVerificationResendRequest $request, AuthenticatableContract&MustVerifyEmailContract $user): SymfonyResponse
    {
        return resolveResponseFactory()->noContent();
    }

    /**
     * Before sending shortcut.
     */
    protected function beforeSending(EmailVerificationResendRequest $request, AuthenticatableContract&MustVerifyEmailContract $user): ?SymfonyResponse
    {
        return null;
    }

    /**
     * Retrieve user.
     */
    protected function retrieveUser(EmailVerificationResendRequest $request): AuthenticatableContract&MustVerifyEmailContract
    {
        $user = null;

        if (\count($request->credentials()) > 0) {
            $user = $this->retrieveByCredentials($request);

            if ($user === null) {
                $this->throwRetrieveByCredentialsFailedError($request);
            }
        } else {
            $user = $this->retrieveByGuard($request);
        }

        if ($user === null) {
            throw new HttpException(SymfonyResponse::HTTP_UNAUTHORIZED);
        }

        if (! $user instanceof MustVerifyEmailContract) {
            throw new HttpException(SymfonyResponse::HTTP_FORBIDDEN);
        }

        return $user;
    }

    /**
     * Get user provider.
     */
    protected function userProvider(EmailVerificationResendRequest $request): UserProviderContract
    {
        return inject(AuthService::class)->userProvider(resolveAuthManager()->guard($request->guardName()));
    }

    /**
     * Retrieve user by credentials.
     */
    protected function retrieveByCredentials(EmailVerificationResendRequest $request): ?AuthenticatableContract
    {
        return $this->userProvider($request)->retrieveByCredentials($request->credentials());
    }

    /**
     * Retrieve user by guard.
     */
    protected function retrieveByGuard(EmailVerificationResendRequest $request): ?AuthenticatableContract
    {
        return resolveUser([$request->guardName()]);
    }

    /**
     * Throw retrieve by credentials failed error.
     */
    protected function throwRetrieveByCredentialsFailedError(EmailVerificationResendRequest $request): never
    {
        throw ValidationException::withMessages(\array_map(static fn (): array => [mustTransString('auth.failed')], $request->credentials()));
    }
}

<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Http\Controllers;

use Closure;
use Illuminate\Auth\Events\Verified;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Contracts\Auth\UserProvider as UserProviderContract;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tomchochola\Laratchi\Auth\Http\Requests\EmailVerificationVerifyRequest;
use Tomchochola\Laratchi\Auth\Services\AuthService;
use Tomchochola\Laratchi\Routing\TransactionController;

class EmailVerificationVerifyController extends TransactionController
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
    public function __invoke(EmailVerificationVerifyRequest $request): SymfonyResponse
    {
        $this->hit($this->limit($request, ''), $this->onThrottle($request));

        $user = $this->retrieveUser($request);

        $response = $this->beforeVerifing($request, $user);

        if ($response !== null) {
            return $response;
        }

        $ok = $this->markEmailAsVerified($request, $user);

        if ($ok) {
            $this->fireVerifiedEvent($request, $user);
        }

        return $this->response($request, $user);
    }

    /**
     * Retrieve user.
     */
    public function retrieveUser(EmailVerificationVerifyRequest $request): AuthenticatableContract&MustVerifyEmailContract
    {
        $user = $this->retrieveByCredentials($request);

        if ($user === null) {
            $this->throwRetrieveByCredentialsFailedError($request);
        }

        if (! $user instanceof MustVerifyEmailContract) {
            throw new HttpException(SymfonyResponse::HTTP_FORBIDDEN);
        }

        return $user;
    }

    /**
     * Throttle limit.
     */
    protected function limit(EmailVerificationVerifyRequest $request, string $key): Limit
    {
        return Limit::perMinutes(static::$decay, static::$throttle)->by(requestSignature()->data('key', $key)->hash());
    }

    /**
     * Throttle callback.
     *
     * @return (Closure(int): never)|null
     */
    protected function onThrottle(EmailVerificationVerifyRequest $request): ?Closure
    {
        return static function (int $seconds) use ($request): never {
            if (static::$simpleThrottle) {
                throw new ThrottleRequestsException();
            }

            $request->throwThrottleValidationError(\array_keys($request->credentials()), $seconds);
        };
    }

    /**
     * Mark email as verified.
     */
    protected function markEmailAsVerified(EmailVerificationVerifyRequest $request, AuthenticatableContract&MustVerifyEmailContract $user): bool
    {
        if (! $user->hasVerifiedEmail()) {
            return $user->markEmailAsVerified();
        }

        return false;
    }

    /**
     * Fire verified event.
     */
    protected function fireVerifiedEvent(EmailVerificationVerifyRequest $request, AuthenticatableContract&MustVerifyEmailContract $user): void
    {
        resolveEventDispatcher()->dispatch(new Verified($user));
    }

    /**
     * Make response.
     */
    protected function response(EmailVerificationVerifyRequest $request, AuthenticatableContract&MustVerifyEmailContract $user): SymfonyResponse
    {
        return resolveResponseFactory()->noContent();
    }

    /**
     * Before verifing shortcut.
     */
    protected function beforeVerifing(EmailVerificationVerifyRequest $request, AuthenticatableContract&MustVerifyEmailContract $user): ?SymfonyResponse
    {
        return null;
    }

    /**
     * Get user provider.
     */
    protected function userProvider(EmailVerificationVerifyRequest $request): UserProviderContract
    {
        return inject(AuthService::class)->userProvider(resolveAuthManager()->guard($request->guardName()));
    }

    /**
     * Retrieve user by credentials.
     */
    protected function retrieveByCredentials(EmailVerificationVerifyRequest $request): ?AuthenticatableContract
    {
        return $this->userProvider($request)->retrieveByCredentials($request->credentials());
    }

    /**
     * Throw retrieve by credentials failed error.
     */
    protected function throwRetrieveByCredentialsFailedError(EmailVerificationVerifyRequest $request): never
    {
        $request->throwValidationException(\array_map(static fn (): array => ['auth.failed' => []], $request->credentials()));
    }
}

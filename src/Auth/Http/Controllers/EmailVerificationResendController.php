<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Http\Controllers;

use Closure;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Tomchochola\Laratchi\Auth\Http\Requests\EmailVerificationResendRequest;
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
     * Handle the incoming request.
     */
    public function __invoke(EmailVerificationResendRequest $request): SymfonyResponse
    {
        $this->hit($this->limit($request, ''), $this->onThrottle($request));

        $this->sendEmailVerificationNotification($request);

        return $this->response($request);
    }

    /**
     * Throttle limit.
     */
    protected function limit(EmailVerificationResendRequest $request, string $key): Limit
    {
        return Limit::perMinutes(static::$decay, static::$throttle)->by(requestSignature()->data('key', $key)->user($request->retrieveUser())->hash());
    }

    /**
     * Throttle callback.
     *
     * @return (Closure(int): never)|null
     */
    protected function onThrottle(EmailVerificationResendRequest $request): ?Closure
    {
        return static function (): never {
            throw new ThrottleRequestsException();
        };
    }

    /**
     * Send email verification notification.
     */
    protected function sendEmailVerificationNotification(EmailVerificationResendRequest $request): void
    {
        $user = $request->retrieveUser();

        if (! $user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }
    }

    /**
     * Make response.
     */
    protected function response(EmailVerificationResendRequest $request): SymfonyResponse
    {
        return resolveResponseFactory()->noContent();
    }
}

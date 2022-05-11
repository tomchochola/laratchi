<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Http\Controllers;

use Closure;
use Illuminate\Auth\Events\Verified;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Tomchochola\Laratchi\Auth\Http\Requests\EmailVerificationVerifyRequest;
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
     * Handle the incoming request.
     */
    public function __invoke(EmailVerificationVerifyRequest $request): SymfonyResponse
    {
        $this->hit($this->limit($request, ''), $this->onThrottle($request));

        $response = $this->beforeVerifing($request);

        if ($response !== null) {
            return $response;
        }

        $ok = $this->markEmailAsVerified($request);

        if ($ok) {
            $this->fireVerifiedEvent($request);
        }

        return $this->response($request);
    }

    /**
     * Throttle limit.
     */
    protected function limit(EmailVerificationVerifyRequest $request, string $key): Limit
    {
        return Limit::perMinutes(static::$decay, static::$throttle)->by(requestSignature()->data('key', $key)->user($request->retrieveUser())->hash());
    }

    /**
     * Throttle callback.
     *
     * @return (Closure(int): never)|null
     */
    protected function onThrottle(EmailVerificationVerifyRequest $request): ?Closure
    {
        return static function (): never {
            throw new ThrottleRequestsException();
        };
    }

    /**
     * Mark email as verified.
     */
    protected function markEmailAsVerified(EmailVerificationVerifyRequest $request): bool
    {
        $user = $request->retrieveUser();

        if (! $user->hasVerifiedEmail()) {
            return $user->markEmailAsVerified();
        }

        return false;
    }

    /**
     * Fire verified event.
     */
    protected function fireVerifiedEvent(EmailVerificationVerifyRequest $request): void
    {
        resolveEventDispatcher()->dispatch(new Verified($request->retrieveUser()));
    }

    /**
     * Make response.
     */
    protected function response(EmailVerificationVerifyRequest $request): SymfonyResponse
    {
        return resolveResponseFactory()->noContent();
    }

    /**
     * Before verifing shortcut.
     */
    protected function beforeVerifing(EmailVerificationVerifyRequest $request): ?SymfonyResponse
    {
        return null;
    }
}

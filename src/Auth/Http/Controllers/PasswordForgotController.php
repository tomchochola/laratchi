<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Http\Controllers;

use Closure;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Auth\PasswordBroker as PasswordBrokerContract;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Tomchochola\Laratchi\Auth\Http\Requests\PasswordForgotRequest;
use Tomchochola\Laratchi\Routing\TransactionController;

class PasswordForgotController extends TransactionController
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
    public function __invoke(PasswordForgotRequest $request): SymfonyResponse
    {
        [$hit] = $this->throttle($this->limit($request, 'status'), $this->onThrottle($request));

        $response = $this->beforeSending($request);

        if ($response !== null) {
            return $response;
        }

        $status = $this->sendResetLink($request);

        if ($status !== PasswordBrokerContract::RESET_LINK_SENT) {
            $hit();

            $this->throwInvalidStatus($request, $status);
        }

        return $this->response($request);
    }

    /**
     * Throttle limit.
     */
    protected function limit(PasswordForgotRequest $request, string $key): Limit
    {
        return Limit::perMinutes(static::$decay, static::$throttle)->by(requestSignature()->data('key', $key)->hash());
    }

    /**
     * Throttle callback.
     *
     * @return (Closure(int): never)|null
     */
    protected function onThrottle(PasswordForgotRequest $request): ?Closure
    {
        return function (int $seconds) use ($request): never {
            $this->throwThrottleValidationError(\array_keys($request->credentials()), $seconds);
        };
    }

    /**
     * Send reset link.
     */
    protected function sendResetLink(PasswordForgotRequest $request): string
    {
        return $this->passwordBroker($request)->sendResetLink($request->credentials());
    }

    /**
     * Get password broker.
     */
    protected function passwordBroker(PasswordForgotRequest $request): PasswordBrokerContract
    {
        return resolvePasswordBrokerManager()->broker($request->passwordBrokerName());
    }

    /**
     * Throw invalid status error.
     */
    protected function throwInvalidStatus(PasswordForgotRequest $request, string $status): never
    {
        throw ValidationException::withMessages(\array_map(static fn (): array => [mustTransString($status)], $request->credentials()));
    }

    /**
     * Make response.
     */
    protected function response(PasswordForgotRequest $request): SymfonyResponse
    {
        return resolveResponseFactory()->noContent();
    }

    /**
     * Before sending shortcut.
     */
    protected function beforeSending(PasswordForgotRequest $request): ?SymfonyResponse
    {
        return null;
    }
}

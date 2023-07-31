<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Http\Controllers;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\PasswordBroker as PasswordBrokerContract;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Tomchochola\Laratchi\Auth\Http\Requests\PasswordForgotRequest;
use Tomchochola\Laratchi\Auth\User;
use Tomchochola\Laratchi\Routing\TransactionController;

class PasswordForgotController extends TransactionController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(PasswordForgotRequest $request): SymfonyResponse
    {
        $me = $this->me($request);

        $this->validateToken($request, $me);

        $this->send($request, $me);

        return $this->response($request, $me);
    }

    /**
     * Make response.
     */
    protected function response(PasswordForgotRequest $request, User $me): SymfonyResponse
    {
        return resolveResponseFactory()->noContent(202);
    }

    /**
     * Send password reset notification.
     */
    protected function send(PasswordForgotRequest $request, User $me): void
    {
        $me->sendPasswordResetNotification(resolvePasswordBroker()->createToken($me));
    }

    /**
     * Me.
     */
    protected function me(PasswordForgotRequest $request): User
    {
        $credentials = $request->credentials();

        [$hit] = $this->throttle($this->limit('credentials'), $this->onThrottle($request, \array_keys($credentials), 'auth.throttle'));

        $me = resolveUserProvider()->retrieveByCredentials($credentials);

        if (! $me instanceof User) {
            $hit();
            $request->throwSingleValidationException(\array_keys($credentials), 'auth.failed');
        }

        if ($me->getEmailForPasswordReset() === '') {
            throw new AuthorizationException();
        }

        return $me;
    }

    /**
     * Validate token.
     */
    protected function validateToken(PasswordForgotRequest $request, User $me): void
    {
        if (
            resolvePasswordBroker()
                ->getRepository()
                ->recentlyCreatedToken($me)
        ) {
            $request->throwSingleValidationException(\array_keys($request->credentials()), PasswordBrokerContract::RESET_THROTTLED);
        }
    }
}

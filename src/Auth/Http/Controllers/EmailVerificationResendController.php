<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Http\Controllers;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Tomchochola\Laratchi\Auth\Http\Requests\EmailVerificationResendRequest;
use Tomchochola\Laratchi\Auth\User;
use Tomchochola\Laratchi\Routing\TransactionController;

class EmailVerificationResendController extends TransactionController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(EmailVerificationResendRequest $request): SymfonyResponse
    {
        $me = $this->me($request);

        $this->hasVerifiedEmail($request, $me);

        $this->send($request, $me);

        return $this->response($request, $me);
    }

    /**
     * Me.
     */
    protected function me(EmailVerificationResendRequest $request): User&MustVerifyEmail
    {
        $credentials = $request->credentials();

        [$hit] = $this->throttle($this->limit('credentials'), $this->onThrottle($request, \array_keys($credentials)));

        if (\count($credentials) > 0) {
            $me = resolveUserProvider()->retrieveByCredentials($credentials);

            if (! $me instanceof User) {
                $hit();
                $request->throwSingleValidationException(\array_keys($credentials), 'auth.failed');
            }
        } else {
            $me = $request->mustAuth();
        }

        if (! $me instanceof MustVerifyEmail || $me->getEmailForVerification() === '') {
            throw new AuthorizationException();
        }

        return $me;
    }

    /**
     * Send email verification notification.
     */
    protected function send(EmailVerificationResendRequest $request, User&MustVerifyEmail $me): void
    {
        $this->hit($this->limit('send'), $this->onThrottle($request));

        $me->sendEmailVerificationNotification();
    }

    /**
     * Make response.
     */
    protected function response(EmailVerificationResendRequest $request, User&MustVerifyEmail $me): SymfonyResponse
    {
        return resolveResponseFactory()->noContent(202);
    }

    /**
     * Check user has verified email.
     */
    protected function hasVerifiedEmail(EmailVerificationResendRequest $request, User&MustVerifyEmail $me): void
    {
        if ($me->hasVerifiedEmail()) {
            throw new ConflictHttpException();
        }
    }
}

<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Http\Controllers;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Tomchochola\Laratchi\Auth\Http\Requests\EmailVerificationVerifyRequest;
use Tomchochola\Laratchi\Auth\Services\EmailBrokerService;
use Tomchochola\Laratchi\Auth\User;
use Tomchochola\Laratchi\Routing\TransactionController;

class EmailVerificationVerifyController extends TransactionController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(EmailVerificationVerifyRequest $request): SymfonyResponse
    {
        $me = $this->me($request);

        $this->hasVerifiedEmail($request, $me);

        $this->validateToken($request, $me);

        $this->verify($request, $me);

        return $this->response($request, $me);
    }

    /**
     * Me.
     */
    protected function me(EmailVerificationVerifyRequest $request): User&MustVerifyEmail
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
     * Mark email as verified.
     */
    protected function verify(EmailVerificationVerifyRequest $request, User&MustVerifyEmail $me): void
    {
        $me->markEmailAsVerified();
    }

    /**
     * Make response.
     */
    protected function response(EmailVerificationVerifyRequest $request, User&MustVerifyEmail $me): SymfonyResponse
    {
        return resolveResponseFactory()->noContent();
    }

    /**
     * Check user has verified email.
     */
    protected function hasVerifiedEmail(EmailVerificationVerifyRequest $request, User&MustVerifyEmail $me): void
    {
        if ($me->hasVerifiedEmail()) {
            throw new ConflictHttpException();
        }
    }

    /**
     * Validate token.
     */
    protected function validateToken(EmailVerificationVerifyRequest $request, User&MustVerifyEmail $me): void
    {
        [$hit] = $this->throttle($this->limit('credentials'), $this->onThrottle($request, ['token']));

        if (! EmailBrokerService::inject()->validate($me->getTable(), $me->getEmailForVerification(), $request->validatedInput()->mustString('token'))) {
            $hit();
            $request->throwExistsValidationException(['token']);
        }
    }
}

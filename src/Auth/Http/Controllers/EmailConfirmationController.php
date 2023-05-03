<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Http\Controllers;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Tomchochola\Laratchi\Auth\Http\Requests\EmailConfirmationRequest;
use Tomchochola\Laratchi\Auth\Services\EmailBrokerService;
use Tomchochola\Laratchi\Auth\User;
use Tomchochola\Laratchi\Routing\TransactionController;

class EmailConfirmationController extends TransactionController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(EmailConfirmationRequest $request): SymfonyResponse
    {
        $this->hasVerifiedEmail($request);

        $this->validateToken($request);

        $this->verify($request);

        return $this->response($request);
    }

    /**
     * Mark email as verified.
     */
    protected function verify(EmailConfirmationRequest $request): void
    {
        EmailBrokerService::inject()->confirm(resolveAuthManager()->getDefaultDriver(), $request->validatedInput()->mustString('email'));
    }

    /**
     * Make response.
     */
    protected function response(EmailConfirmationRequest $request): SymfonyResponse
    {
        return resolveResponseFactory()->noContent();
    }

    /**
     * Check user has verified email.
     */
    protected function hasVerifiedEmail(EmailConfirmationRequest $request): void
    {
        if (EmailBrokerService::inject()->confirmed(resolveAuthManager()->getDefaultDriver(), $request->validatedInput()->mustString('email'))) {
            throw new ConflictHttpException();
        }
    }

    /**
     * Validate token.
     */
    protected function validateToken(EmailConfirmationRequest $request): void
    {
        [$hit] = $this->throttle($this->limit('token'), $this->onThrottle($request, ['token']));

        if (! EmailBrokerService::inject()->validate(resolveAuthManager()->getDefaultDriver(), $request->validatedInput()->mustString('email'), $request->validatedInput()->mustString('token'))) {
            $hit();
            $request->throwExistsValidationException(['token']);
        }
    }
}

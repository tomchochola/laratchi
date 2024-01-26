<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Http\Controllers;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Tomchochola\Laratchi\Auth\Http\Requests\EmailConfirmationResendRequest;
use Tomchochola\Laratchi\Auth\Services\EmailBrokerService;
use Tomchochola\Laratchi\Auth\User;
use Tomchochola\Laratchi\Config\Config;
use Tomchochola\Laratchi\Routing\TransactionController;

class EmailConfirmationResendController extends TransactionController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(EmailConfirmationResendRequest $request): SymfonyResponse
    {
        $this->validateUnique($request);

        $this->hasVerifiedEmail($request);

        $this->send($request);

        return $this->response($request);
    }

    /**
     * Make response.
     */
    protected function response(EmailConfirmationResendRequest $request): SymfonyResponse
    {
        return \resolveResponseFactory()->noContent(202);
    }

    /**
     * Check user has verified email.
     */
    protected function hasVerifiedEmail(EmailConfirmationResendRequest $request): void
    {
        if (EmailBrokerService::inject()->confirmed(Config::inject()->authDefaultsGuard(), $request->validatedInput()->mustString('email'))) {
            throw new ConflictHttpException();
        }
    }

    /**
     * Validate given credentials are unique.
     */
    protected function validateUnique(EmailConfirmationResendRequest $request): void
    {
        $credentials = ['email' => $request->validatedInput()->mustString('email')];

        [$hit] = $this->throttle($this->limit(__METHOD__), $this->onThrottle($request, \array_keys($credentials)));

        $user = \resolveUserProvider()->retrieveByCredentials($credentials);

        if ($user !== null) {
            $hit();
            $request->throwUniqueValidationException(\array_keys($credentials));
        }
    }

    /**
     * Send email verification notification.
     */
    protected function send(EmailConfirmationResendRequest $request): void
    {
        $email = $request->validatedInput()->mustString('email');

        $guard = Config::inject()->authDefaultsGuard();
        $broker = EmailBrokerService::inject();

        $this->hit($this->limit('email_confirmation_send'), $this->onThrottle($request, ['email']));

        $broker->anonymous($guard, $email, Config::inject()->appLocale());
    }
}

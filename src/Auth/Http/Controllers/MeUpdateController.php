<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Http\Controllers;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Tomchochola\Laratchi\Auth\Http\Requests\MeUpdateRequest;
use Tomchochola\Laratchi\Auth\Services\EmailBrokerService;
use Tomchochola\Laratchi\Auth\User;
use Tomchochola\Laratchi\Routing\TransactionController;

class MeUpdateController extends TransactionController
{
    /**
     * E-mail confirmation.
     */
    public static bool $emailConfirmation = true;

    /**
     * Handle the incoming request.
     */
    public function __invoke(MeUpdateRequest $request): SymfonyResponse
    {
        $me = $this->me($request);

        $this->validateUnique($request, $me);

        $shortcut = $this->shortcut($request, $me);

        if ($shortcut !== null) {
            return $shortcut;
        }

        $shortcut = $this->emailConfirmation($request, $me);

        if ($shortcut !== null) {
            return $shortcut;
        }

        $this->update($request, $me);

        $this->passwordInit($request, $me);

        $this->emailVerification($request, $me);

        return $this->response($request, $me);
    }

    /**
     * Make response.
     */
    protected function response(MeUpdateRequest $request, User $me): SymfonyResponse
    {
        return resolveResponseFactory()->noContent();
    }

    /**
     * Validate given credentials are unique.
     */
    protected function validateUnique(MeUpdateRequest $request, User $me): void
    {
        $credentialsArray = $request->credentials();

        foreach ($credentialsArray as $index => $credentials) {
            [$hit] = $this->throttle($this->limit("credentials.{$index}"), $this->onThrottle($request, \array_keys($credentials)));

            $user = resolveUserProvider()->retrieveByCredentials($credentials);

            if ($user instanceof User && $user->isNot($me)) {
                $hit();
                $request->throwUniqueValidationException(\array_keys($credentials));
            }
        }
    }

    /**
     * Update user.
     */
    protected function update(MeUpdateRequest $request, User $me): void
    {
        $me->update($request->data());
    }

    /**
     * Shortcut.
     */
    protected function shortcut(MeUpdateRequest $request, User $me): ?SymfonyResponse
    {
        return null;
    }

    /**
     * Me.
     */
    protected function me(MeUpdateRequest $request): User
    {
        return $request->mustAuth();
    }

    /**
     * Email confirmation.
     */
    protected function emailConfirmation(MeUpdateRequest $request, User $me): ?SymfonyResponse
    {
        if (static::$emailConfirmation === false) {
            return null;
        }

        $email = $request->validatedInput()->string('email');

        if ($email === null) {
            return null;
        }

        $token = $request->validatedInput()->string('token');

        $guard = resolveAuthManager()->getDefaultDriver();
        $broker = EmailBrokerService::inject();

        if ($token === null) {
            $this->hit($this->limit('email_confirmation_send'), $this->onThrottle($request, ['token']));

            $broker->send($guard, $email, resolveApp()->getLocale());

            return resolveResponseFactory()->noContent(202);
        }

        [$hit] = $this->throttle($this->limit('email_confirmation_validate'), $this->onThrottle($request, ['token']));

        if (! $broker->validate($guard, $email, $token)) {
            $hit();
            $request->throwExistsValidationException(['token']);
        }

        return null;
    }

    /**
     * Password init.
     */
    protected function passwordInit(MeUpdateRequest $request, User $me): void
    {
        if ($me->wasChanged('email') && $me->getAuthPassword() === '' && $me->getEmailForPasswordReset() !== '') {
            $this->hit($this->limit('password_init'), $this->onThrottle($request, ['email']));

            $me->sendPasswordInitNotification(resolvePasswordBroker($me->getTable())->createToken($me));
        }
    }

    /**
     * E-mail verification.
     */
    protected function emailVerification(MeUpdateRequest $request, User $me): void
    {
        if ($me instanceof MustVerifyEmail && $me->wasChanged('email') && ! $me->hasVerifiedEmail() && $me->getEmailForVerification() !== '') {
            $this->hit($this->limit('email_verification'), $this->onThrottle($request, ['email']));

            $me->sendEmailVerificationNotification();
        }
    }
}

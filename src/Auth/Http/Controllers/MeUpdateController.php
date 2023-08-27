<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Http\Controllers;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Tomchochola\Laratchi\Auth\Http\Requests\MeUpdateRequest;
use Tomchochola\Laratchi\Auth\Services\EmailBrokerService;
use Tomchochola\Laratchi\Auth\User;
use Tomchochola\Laratchi\Config\Config;
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

        $guard = Config::inject()->authDefaultsGuard();
        $broker = EmailBrokerService::inject();

        if ($broker->confirmed($guard, $email)) {
            return null;
        }

        $this->hit($this->limit('email_confirmation_send'), $this->onThrottle($request, ['email']));

        $broker->anonymous($guard, $email, Config::inject()->appLocale());

        return resolveResponseFactory()->noContent(202);
    }
}

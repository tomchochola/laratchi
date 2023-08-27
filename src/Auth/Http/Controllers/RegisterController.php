<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Http\Controllers;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Tomchochola\Laratchi\Auth\Http\Requests\RegisterRequest;
use Tomchochola\Laratchi\Auth\Services\CanLoginService;
use Tomchochola\Laratchi\Auth\Services\EmailBrokerService;
use Tomchochola\Laratchi\Auth\User;
use Tomchochola\Laratchi\Config\Config;
use Tomchochola\Laratchi\Routing\TransactionController;

class RegisterController extends TransactionController
{
    /**
     * E-mail confirmation.
     */
    public static bool $emailConfirmation = true;

    /**
     * Handle the incoming request.
     */
    public function __invoke(RegisterRequest $request): SymfonyResponse
    {
        $this->validateUnique($request);

        $shortcut = $this->shortcut($request);

        if ($shortcut !== null) {
            return $shortcut;
        }

        $shortcut = $this->emailConfirmation($request);

        if ($shortcut !== null) {
            return $shortcut;
        }

        $me = $this->store($request);

        $shortcut = $this->canLogin($request, $me);

        if ($shortcut !== null) {
            return $shortcut;
        }

        $this->login($request, $me);

        return $this->response($request, $me);
    }

    /**
     * Login.
     */
    protected function login(RegisterRequest $request, User $me): void
    {
        resolveGuard()->login($me);
    }

    /**
     * Check if user can login.
     */
    protected function canLogin(RegisterRequest $request, User $me): ?SymfonyResponse
    {
        if (
            CanLoginService::inject()
                ->authorize($me)
                ->denied()
        ) {
            return resolveResponseFactory()->noContent();
        }

        return null;
    }

    /**
     * Make response.
     */
    protected function response(RegisterRequest $request, User $me): SymfonyResponse
    {
        return $me->meResource()->response();
    }

    /**
     * Create new user.
     */
    protected function store(RegisterRequest $request): User
    {
        $me = assertInstance(resolveUserProvider()->createModel(), User::class);

        $me->fill($request->data());

        $me->save();

        return $me->refresh();
    }

    /**
     * Validate given credentials are unique.
     */
    protected function validateUnique(RegisterRequest $request): void
    {
        $credentialsArray = $request->credentials();

        foreach ($credentialsArray as $index => $credentials) {
            [$hit] = $this->throttle($this->limit("credentials.{$index}"), $this->onThrottle($request, \array_keys($credentials)));

            $user = resolveUserProvider()->retrieveByCredentials($credentials);

            if ($user instanceof User) {
                $hit();
                $request->throwUniqueValidationException(\array_keys($credentials));
            }
        }
    }

    /**
     * Shortcut.
     */
    protected function shortcut(RegisterRequest $request): ?SymfonyResponse
    {
        return null;
    }

    /**
     * Email confirmation.
     */
    protected function emailConfirmation(RegisterRequest $request): ?SymfonyResponse
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

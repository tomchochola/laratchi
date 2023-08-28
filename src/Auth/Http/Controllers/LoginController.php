<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Http\Controllers;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Tomchochola\Laratchi\Auth\Http\Requests\LoginRequest;
use Tomchochola\Laratchi\Auth\Services\CanLoginService;
use Tomchochola\Laratchi\Auth\User;
use Tomchochola\Laratchi\Routing\TransactionController;

class LoginController extends TransactionController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(LoginRequest $request): SymfonyResponse
    {
        $me = $this->me($request);

        $this->validatePassword($request, $me);

        $this->canLogin($request, $me);

        $this->login($request, $me);

        return $this->response($request, $me);
    }

    /**
     * Validate password.
     */
    protected function validatePassword(LoginRequest $request, User $me): void
    {
        $password = $request->validatedInput()->string('password');

        if ($password === null) {
            return;
        }

        [$hit] = $this->throttle($this->limit('password'), $this->onThrottle($request, ['password'], 'auth.throttle'));

        if (! resolveHasher()->check($password, $me->getAuthPassword())) {
            $hit();
            $request->throwSingleValidationException(['password'], 'auth.password');
        }
    }

    /**
     * Check if user can login.
     */
    protected function canLogin(LoginRequest $request, User $me): void
    {
        $response = CanLoginService::inject()->authorize($me);

        if ($response->allowed()) {
            return;
        }

        $code = $response->code();

        if ($code !== null) {
            $response->authorize();
        }

        $message = $response->message() ?? '';

        $request->throwSingleValidationException(\array_keys($request->credentials()), $message === '' ? 'auth.blocked' : $message);
    }

    /**
     * Login.
     */
    protected function login(LoginRequest $request, User $me): void
    {
        resolveGuard()->login($me);
    }

    /**
     * Make response.
     */
    protected function response(LoginRequest $request, User $me): SymfonyResponse
    {
        return $me->meResource()->response();
    }

    /**
     * Me.
     */
    protected function me(LoginRequest $request): User
    {
        $credentialsArray = \array_merge([$request->credentials()], $request->multipleCredentials());

        foreach ($credentialsArray as $index => $credentials) {
            [$hit] = $this->throttle($this->limit("credentials.{$index}"), $this->onThrottle($request, \array_keys($credentials), 'auth.throttle'));

            $user = resolveUserProvider()->retrieveByCredentials($credentials);

            if ($user instanceof User) {
                return $user;
            }

            $hit();
        }

        $request->throwSingleValidationException(\array_keys($request->credentials()), 'auth.failed');
    }
}

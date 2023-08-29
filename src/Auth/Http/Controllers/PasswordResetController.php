<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Http\Controllers;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\PasswordBroker;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Tomchochola\Laratchi\Auth\Http\Requests\PasswordResetRequest;
use Tomchochola\Laratchi\Auth\Services\CanLoginService;
use Tomchochola\Laratchi\Auth\User;
use Tomchochola\Laratchi\Config\Config;
use Tomchochola\Laratchi\Routing\TransactionController;

class PasswordResetController extends TransactionController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(PasswordResetRequest $request): SymfonyResponse
    {
        $me = $this->me($request);

        $this->validateToken($request, $me);

        $this->canLogin($request, $me);

        $this->reset($request, $me);

        $this->deleteToken($request, $me);

        $this->login($request, $me);

        return $this->response($request, $me);
    }

    /**
     * Make response.
     */
    protected function response(PasswordResetRequest $request, User $me): SymfonyResponse
    {
        return $me->meResource()->response();
    }

    /**
     * Check if user can login.
     */
    protected function canLogin(PasswordResetRequest $request, User $me): void
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
     * Validate password.
     */
    protected function validateToken(PasswordResetRequest $request, User $me): void
    {
        [$hit] = $this->throttle($this->limit('token'), $this->onThrottle($request, ['token'], PasswordBroker::RESET_THROTTLED));

        $token = $request->validatedInput()->mustString('token');

        if (!Config::inject()->appEnvIs(['production']) && $token === '111111') {
            return;
        }

        if (!\resolvePasswordBroker()->tokenExists($me, $token)) {
            $hit();
            $request->throwSingleValidationException(['token'], PasswordBroker::INVALID_TOKEN);
        }
    }

    /**
     * Reset password.
     */
    protected function reset(PasswordResetRequest $request, User $me): void
    {
        $me->update(['password' => \resolveHasher()->make($request->validatedInput()->mustString('password'))]);
    }

    /**
     * Login.
     */
    protected function login(PasswordResetRequest $request, User $me): void
    {
        \resolveGuard()->login($me);
    }

    /**
     * Me.
     */
    protected function me(PasswordResetRequest $request): User
    {
        $credentials = $request->credentials();

        [$hit] = $this->throttle($this->limit('credentials'), $this->onThrottle($request, \array_keys($credentials), 'auth.throttle'));

        $me = \resolveUserProvider()->retrieveByCredentials($credentials);

        if (!$me instanceof User) {
            $hit();
            $request->throwSingleValidationException(\array_keys($credentials), 'auth.failed');
        }

        if ($me->getEmailForPasswordReset() === '') {
            throw new AuthorizationException();
        }

        return $me;
    }

    /**
     * Delete token.
     */
    protected function deleteToken(PasswordResetRequest $request, User $me): void
    {
        \resolvePasswordBroker()->deleteToken($me);
    }
}

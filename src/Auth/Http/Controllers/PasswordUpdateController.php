<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Http\Controllers;

use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Tomchochola\Laratchi\Auth\Http\Requests\PasswordUpdateRequest;
use Tomchochola\Laratchi\Auth\User;
use Tomchochola\Laratchi\Routing\TransactionController;

class PasswordUpdateController extends TransactionController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(PasswordUpdateRequest $request): SymfonyResponse
    {
        $me = $this->me($request);

        $this->validatePassword($request, $me);

        $this->update($request, $me);

        $this->login($request, $me);

        return $this->response($request, $me);
    }

    /**
     * Update password.
     */
    protected function update(PasswordUpdateRequest $request, User $me): void
    {
        $me->update(['password' => \resolveHasher()->make($request->validatedInput()->mustString('new_password'))]);

        if ($me->getRememberToken() !== '') {
            \resolveUserProvider()->updateRememberToken($me, Str::random(60));
        }

        $me->databaseTokens()
            ->getQuery()
            ->delete();
    }

    /**
     * Make response.
     */
    protected function response(PasswordUpdateRequest $request, User $me): SymfonyResponse
    {
        return $me->meResource()->response();
    }

    /**
     * Validate password.
     */
    protected function validatePassword(PasswordUpdateRequest $request, User $me): void
    {
        [$hit] = $this->throttle($this->limit('password'), $this->onThrottle($request, ['password'], 'auth.throttle'));

        if (!\resolveHasher()->check($request->validatedInput()->mustString('password'), $me->getAuthPassword())) {
            $hit();
            $request->throwSingleValidationException(['password'], 'auth.password');
        }
    }

    /**
     * Login.
     */
    protected function login(PasswordUpdateRequest $request, User $me): void
    {
        \resolveGuard()->login($me);
    }

    /**
     * Me.
     */
    protected function me(PasswordUpdateRequest $request): User
    {
        return $request->mustAuth();
    }
}

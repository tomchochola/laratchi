<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Http\Controllers;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Tomchochola\Laratchi\Auth\Http\Requests\MeDestroyRequest;
use Tomchochola\Laratchi\Auth\User;
use Tomchochola\Laratchi\Routing\TransactionController;

class MeDestroyController extends TransactionController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(MeDestroyRequest $request): SymfonyResponse
    {
        $me = $this->me($request);

        $this->validatePassword($request, $me);

        $this->logout($request, $me);

        $this->delete($request, $me);

        return $this->response($request, $me);
    }

    /**
     * Response.
     */
    protected function response(MeDestroyRequest $request, User $me): SymfonyResponse
    {
        return \resolveResponseFactory()->noContent();
    }

    /**
     * Logout.
     */
    protected function logout(MeDestroyRequest $request, User $me): void
    {
        \resolveGuard()->logout();
    }

    /**
     * Delete me.
     */
    protected function delete(MeDestroyRequest $request, User $me): void
    {
        $me->delete();
    }

    /**
     * Validate password.
     */
    protected function validatePassword(MeDestroyRequest $request, User $me): void
    {
        [$hit] = $this->throttle($this->limit('password'), $this->onThrottle($request, ['password'], 'auth.throttle'));

        if (!\resolveHasher()->check($request->validatedInput()->mustString('password'), $me->getAuthPassword())) {
            $hit();
            $request->throwSingleValidationException(['password'], 'auth.password');
        }
    }

    /**
     * Me.
     */
    protected function me(MeDestroyRequest $request): User
    {
        return $request->mustAuth();
    }
}

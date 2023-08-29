<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Http\Controllers;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Tomchochola\Laratchi\Auth\Http\Requests\LogoutRequest;
use Tomchochola\Laratchi\Auth\User;
use Tomchochola\Laratchi\Routing\TransactionController;

class LogoutController extends TransactionController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(LogoutRequest $request): SymfonyResponse
    {
        $me = $this->me($request);

        $this->logout($request, $me);

        return $this->response($request, $me);
    }

    /**
     * Logout current device.
     */
    protected function logout(LogoutRequest $request, User $me): void
    {
        \resolveGuard()->logout();
    }

    /**
     * Make response.
     */
    protected function response(LogoutRequest $request, User $me): SymfonyResponse
    {
        return \resolveResponseFactory()->noContent();
    }

    /**
     * Me.
     */
    protected function me(LogoutRequest $request): User
    {
        return $request->mustAuth();
    }
}

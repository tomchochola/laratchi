<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Http\Controllers;

use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Tomchochola\Laratchi\Auth\Http\Requests\MeShowRequest;
use Tomchochola\Laratchi\Auth\Services\AuthService;
use Tomchochola\Laratchi\Providers\LaratchiServiceProvider;
use Tomchochola\Laratchi\Routing\Controller;

class MeShowController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(MeShowRequest $request): SymfonyResponse
    {
        return $this->response($request);
    }

    /**
     * Make response.
     */
    protected function response(MeShowRequest $request): SymfonyResponse
    {
        $user = $request->resolveMe();

        if ($user === null) {
            return $this->unauthenticatedResponse($request);
        }

        return $this->authenticatedResponse($request, $user);
    }

    /**
     * Modify user before response.
     */
    protected function modifyUser(MeShowRequest $request, AuthenticatableContract $user): AuthenticatableContract
    {
        return inject(AuthService::class)->modifyUser($user);
    }

    /**
     * Return unauthenticated response.
     */
    protected function unauthenticatedResponse(MeShowRequest $request): SymfonyResponse
    {
        return resolveResponseFactory()->noContent();
    }

    /**
     * Return authenticated response.
     */
    protected function authenticatedResponse(MeShowRequest $request, AuthenticatableContract $user): SymfonyResponse
    {
        $user = $this->modifyUser($request, $user);

        return (new LaratchiServiceProvider::$meResource($user))->toResponse($request);
    }
}

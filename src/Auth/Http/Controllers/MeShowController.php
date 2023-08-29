<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Http\Controllers;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Tomchochola\Laratchi\Auth\Http\Requests\MeShowRequest;
use Tomchochola\Laratchi\Auth\User;
use Tomchochola\Laratchi\Routing\Controller;

class MeShowController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(MeShowRequest $request): SymfonyResponse
    {
        $me = $this->me($request);

        return $this->response($request, $me);
    }

    /**
     * Me.
     */
    protected function me(MeShowRequest $request): User|null
    {
        return $request->auth();
    }

    /**
     * Make response.
     */
    protected function response(MeShowRequest $request, User|null $me): SymfonyResponse
    {
        if ($me === null) {
            return \resolveResponseFactory()->noContent();
        }

        return $me->meResource()->response();
    }
}

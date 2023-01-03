<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Http\Controllers;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Tomchochola\Laratchi\Auth\Http\Requests\MeShowRequest;
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
        $user = $request->retrieveUser();

        if ($user === null) {
            return resolveResponseFactory()->noContent();
        }

        return (new LaratchiServiceProvider::$meJsonApiResource($user))->toResponse($request);
    }
}

<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Tomchochola\Laratchi\Auth\Actions\LogoutCurrentDeviceActionAction;
use Tomchochola\Laratchi\Auth\Http\Requests\MeDestroyRequest;
use Tomchochola\Laratchi\Routing\Controller;

class MeDestroyController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(MeDestroyRequest $request): SymfonyResponse
    {
        $this->logoutCurrentDevice($request);

        $this->destroyMe($request);

        return $this->response($request);
    }

    /**
     * Make response.
     */
    protected function response(MeDestroyRequest $request): SymfonyResponse
    {
        return resolveResponseFactory()->noContent();
    }

    /**
     * Logout current device.
     */
    protected function logoutCurrentDevice(MeDestroyRequest $request): void
    {
        inject(LogoutCurrentDeviceActionAction::class)->handle($request->guardName());
    }

    /**
     * Destroy me.
     */
    protected function destroyMe(MeDestroyRequest $request): void
    {
        $user = $request->retrieveUser();

        \assert($user instanceof Model);

        $ok = $user->delete();

        \assert($ok === true);
    }
}

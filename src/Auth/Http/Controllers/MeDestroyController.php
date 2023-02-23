<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Tomchochola\Laratchi\Auth\Actions\LogoutCurrentDeviceActionAction;
use Tomchochola\Laratchi\Auth\Http\Requests\MeDestroyRequest;
use Tomchochola\Laratchi\Routing\TransactionController;

class MeDestroyController extends TransactionController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(MeDestroyRequest $request): SymfonyResponse
    {
        $this->destroyMe($request);

        $this->logoutCurrentDevice($request);

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

        $user->delete();
    }
}

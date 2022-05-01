<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Http\Controllers;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Tomchochola\Laratchi\Auth\Actions\LogoutCurrentDeviceActionAction;
use Tomchochola\Laratchi\Auth\Http\Requests\LogoutCurrentDeviceRequest;
use Tomchochola\Laratchi\Routing\TransactionController;

class LogoutCurrentDeviceController extends TransactionController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(LogoutCurrentDeviceRequest $request): SymfonyResponse
    {
        $this->logoutCurrentDevice($request);

        return $this->response($request);
    }

    /**
     * Logout current device.
     */
    protected function logoutCurrentDevice(LogoutCurrentDeviceRequest $request): void
    {
        inject(LogoutCurrentDeviceActionAction::class)->handle($request->guardName());
    }

    /**
     * Make response.
     */
    protected function response(LogoutCurrentDeviceRequest $request): SymfonyResponse
    {
        return resolveResponseFactory()->noContent();
    }
}

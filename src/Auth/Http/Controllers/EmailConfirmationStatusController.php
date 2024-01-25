<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Http\Controllers;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Tomchochola\Laratchi\Auth\Http\Requests\EmailConfirmationStatusRequest;
use Tomchochola\Laratchi\Auth\Services\EmailBrokerService;
use Tomchochola\Laratchi\Config\Config;
use Tomchochola\Laratchi\Routing\TransactionController;
use Tomchochola\Laratchi\Support\Resolver;

class EmailConfirmationStatusController extends TransactionController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(EmailConfirmationStatusRequest $request): SymfonyResponse
    {
        return $this->response($request);
    }

    /**
     * Make response.
     */
    protected function response(EmailConfirmationStatusRequest $request): SymfonyResponse
    {
        return Resolver::resolveResponseFactory()->json([
            'data' => EmailBrokerService::inject()->confirmed(Config::inject()->authDefaultsGuard(), $request->validatedInput()->mustString('email')),
        ]);
    }
}

<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Routing;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class TransactionController extends Controller
{
    /**
     * @inheritDoc
     */
    public function callAction(mixed $method, mixed $parameters): SymfonyResponse
    {
        $response = resolveDatabaseManager()
            ->connection()
            ->transaction(fn (): SymfonyResponse => parent::callAction($method, $parameters));

        \assert($response instanceof SymfonyResponse);

        return $response;
    }
}

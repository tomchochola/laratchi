<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Routing;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Tomchochola\Laratchi\Support\Typer;

class TransactionController extends Controller
{
    /**
     * @inheritDoc
     */
    public function callAction(mixed $method, mixed $parameters): SymfonyResponse
    {
        return Typer::assertInstance(\resolveDatabaseManager()->connection()->transaction(fn(): SymfonyResponse => parent::callAction($method, $parameters)), SymfonyResponse::class);
    }
}

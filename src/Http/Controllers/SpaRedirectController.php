<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Http\Controllers;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Tomchochola\Laratchi\Routing\Controller;

class SpaRedirectController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(): SymfonyResponse
    {
        return \resolveResponseFactory()->redirectTo(\mustTransString('spa.url'));
    }
}

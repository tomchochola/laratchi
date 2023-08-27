<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Swagger\Http\Controllers;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Tomchochola\Laratchi\Routing\Controller;
use Tomchochola\Laratchi\Support\Resolver;
use Tomchochola\Laratchi\Support\Typer;

class SwaggerController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(): SymfonyResponse
    {
        $route = Resolver::resolveRoute();

        $url = Typer::assertString($route->parameter('url'));

        return resolveResponseFactory()->view('laratchi::swagger', [
            'url' => $url,
        ]);
    }
}

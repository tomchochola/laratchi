<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Swagger\Http\Controllers;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Tomchochola\Laratchi\Routing\Controller;

class SwaggerController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(): SymfonyResponse
    {
        $route = resolveRouter()->current();

        \assert($route !== null);

        $url = $route->parameter('url');

        \assert(\is_string($url));

        return resolveResponseFactory()->view('laratchi::swagger', [
            'url' => $url,
        ]);
    }
}

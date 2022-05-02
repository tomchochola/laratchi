<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Api\Http\Controllers;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tomchochola\Laratchi\Routing\Controller;

class SwaggerUiController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(): SymfonyResponse
    {
        $route = resolveRouter()->current();

        \assert($route !== null);

        $file = $route->parameter('file');

        if (\is_string($file)) {
            $fileSystem = resolveFilesystem();

            if (! $fileSystem->exists($file)) {
                throw new NotFoundHttpException();
            }

            $spec = $fileSystem->get($file);

            $spec = \str_replace('http://localhost:8000', resolveUrlFactory()->to(''), $spec);

            return resolveResponseFactory()->view('laratchi::swagger_ui', [
                'spec' => $spec,
            ]);
        }

        $url = $route->parameter('url');

        \assert(\is_string($url));

        return resolveResponseFactory()->view('laratchi::swagger_ui', [
            'url' => $url,
        ]);
    }
}

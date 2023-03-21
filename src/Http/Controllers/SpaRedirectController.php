<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Http\Controllers;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tomchochola\Laratchi\Routing\Controller;

class SpaRedirectController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(): SymfonyResponse
    {
        $spa = \filter_var(mustTransString('spa.url'), \FILTER_VALIDATE_URL);

        if ($spa === false) {
            throw new NotFoundHttpException();
        }

        return resolveResponseFactory()->redirectTo($spa);
    }
}

<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Http\Controllers;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tomchochola\Laratchi\Routing\Controller;
use Tomchochola\Laratchi\Translation\Trans;

class SpaRedirectController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(): SymfonyResponse
    {
        $trans = Trans::inject();

        if ($trans->translator->hasForLocale('spa.url')) {
            return resolveResponseFactory()->redirectTo(mustTransString('spa.url'));
        }

        throw new NotFoundHttpException();
    }
}

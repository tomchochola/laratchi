<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Http\Controllers;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tomchochola\Laratchi\Routing\Controller;

class NotFoundController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(): never
    {
        throw new NotFoundHttpException();
    }
}

<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Http;

use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Tomchochola\Laratchi\Http\Middleware\SetPreferredLanguageMiddleware;
use Tomchochola\Laratchi\Http\Middleware\SetRequestFormatMiddleware;
use Tomchochola\Laratchi\Http\Middleware\TrimStrings;
use Tomchochola\Laratchi\Http\Middleware\ValidateAcceptHeaderMiddleware;
use Tomchochola\Laratchi\Http\Middleware\ValidateContentTypeHeaderMiddleware;

class Kernel extends HttpKernel
{
    /**
     * @inheritDoc
     */
    protected $middleware = [
        SetPreferredLanguageMiddleware::class,
        TrustProxies::class,
        HandleCors::class,
        PreventRequestsDuringMaintenance::class,
        ValidatePostSize::class,
        TrimStrings::class,
        ConvertEmptyStringsToNull::class,
        AddQueuedCookiesToResponse::class,
    ];

    /**
     * @inheritDoc
     */
    protected $middlewareGroups = [
        'session' => [StartSession::class, AuthenticateSession::class, ShareErrorsFromSession::class, VerifyCsrfToken::class],

        'api' => [SetRequestFormatMiddleware::class . ':json', ValidateAcceptHeaderMiddleware::class . ':application/json', ValidateContentTypeHeaderMiddleware::class . ':form'],
    ];
}

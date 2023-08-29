<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Tomchochola\Laratchi\Http\Controllers\SpaRedirectController;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * @inheritDoc
     */
    public function boot(): void
    {
        parent::boot();

        $this->routes(static function (): void {
            \resolveRouteRegistrar()->get('/', SpaRedirectController::class);

            \resolveRouteRegistrar()->group(\resolveApp()->basePath('routes/http.php'));
        });
    }
}

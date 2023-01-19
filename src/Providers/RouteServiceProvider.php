<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * @inheritDoc
     */
    public function boot(): void
    {
        parent::boot();

        $this->routes(static function (): void {
            resolveRouter()->view('/', 'laratchi::status', [
                'status' => mustTransJsonString('Hello!'),
                'title' => mustConfigString('app.name'),
            ]);

            resolveRouteRegistrar()->group(resolveApp()->basePath('routes/http.php'));
        });
    }
}

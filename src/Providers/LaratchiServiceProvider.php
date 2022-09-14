<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Providers;

use Illuminate\Support\ServiceProvider;
use Tomchochola\Laratchi\Support\ServiceProvider as VendorLaratchiServiceProvider;

class LaratchiServiceProvider extends ServiceProvider
{
    /**
     * @inheritDoc
     */
    public function register(): void
    {
        parent::register();

        $this->modelRestrictions();
    }

    /**
     * Unguard models and prevent lazy loading.
     */
    protected function modelRestrictions(): void
    {
        VendorLaratchiServiceProvider::modelRestrictions();
    }
}

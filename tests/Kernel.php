<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Tests;

use Orchestra\Testbench\Foundation\Http\Kernel as OrchestraKernel;

class Kernel extends OrchestraKernel
{
    /**
     * The application's middleware stack.
     *
     * @var array<mixed>
     */
    protected $middleware = [];
}

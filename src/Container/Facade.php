<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Container;

use Illuminate\Support\Facades\Facade as IlluminateFacade;

class Facade extends IlluminateFacade
{
    /**
     * Has resolved instance.
     */
    public static function hasResolved(string $abstract): bool
    {
        if (isset(static::$resolvedInstance[$abstract])) {
            return true;
        }

        return false;
    }

    /**
     * Get resolved instance.
     */
    public static function getResolved(string $abstract): object
    {
        return static::$resolvedInstance[$abstract];
    }

    /**
     * Set resolved instance.
     */
    public static function setResolved(string $abstract, object $instance): void
    {
        static::$resolvedInstance[$abstract] = $instance;
    }
}

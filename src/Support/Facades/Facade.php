<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Support\Facades;

use Closure;
use Illuminate\Support\Facades\Facade as IlluminateFacade;

class Facade extends IlluminateFacade
{
    /**
     * Hotswap the underlying instance behind the facade.
     */
    public static function fake(string $facade, mixed $instance): void
    {
        static::$resolvedInstance[$facade] = $instance;
        static::$app->instance($facade, $instance);
    }

    /**
     * Set instance cache.
     */
    public static function instance(string $facade, mixed $instance): void
    {
        static::$resolvedInstance[$facade] = $instance;
    }

    /**
     * After resolving callback.
     */
    public static function afterResolving(string $facade, Closure $closure, bool $cache = true): void
    {
        $app = resolveApp();

        $app->afterResolving($facade, static function (mixed $instance) use ($facade, $closure, $cache): void {
            $closure($instance);

            if ($cache) {
                static::instance($facade, $instance);
            }
        });

        if ($app->resolved($facade)) {
            $instance = $app->make($facade);

            $closure($instance);

            if ($cache) {
                static::instance($facade, $instance);
            }
        }
    }
}

<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Container;

trait InjectTrait
{
    /**
     * Inject cache.
     */
    public static ?self $injectCache = null;

    /**
     * Inject.
     */
    public static function inject(): self
    {
        if (static::$injectCache === null) {
            static::$injectCache = new self();
        }

        return static::$injectCache;
    }
}

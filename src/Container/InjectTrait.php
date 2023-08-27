<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Container;

use Tomchochola\Laratchi\Support\Typer;

trait InjectTrait
{
    /**
     * Inject.
     */
    public static function inject(): self
    {
        if (Facade::hasResolved(self::class)) {
            return Typer::assertInstance(Facade::getResolved(self::class), self::class);
        }

        $instance = new self();

        Facade::setResolved($instance::class, $instance);

        return $instance;
    }
}

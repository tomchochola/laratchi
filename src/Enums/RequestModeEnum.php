<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Enums;

enum RequestModeEnum: int
{
    case DEFAULT = 0;
    case SELECT = 1;
    case COUNT = 2;

    /**
     * Get possible values.
     *
     * @return array<int, int>
     */
    public static function values(): array
    {
        return \array_column(self::cases(), 'value');
    }
}

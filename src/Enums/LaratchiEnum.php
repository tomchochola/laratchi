<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Enums;

enum LaratchiEnum: int
{
    /**
     * Get all values.
     *
     * @return array<int, int>
     */
    public static function values(): array
    {
        return \array_map(static fn (self $case): int => $case->value, static::cases());
    }
}

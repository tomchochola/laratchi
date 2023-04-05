<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\View\Services;

class ViewService
{
    /**
     * Get favicon url.
     */
    public function faviconUrl(): ?string
    {
        return null;
    }

    /**
     * Get color theme.
     */
    public function color(): string
    {
        return '#6c63ff';
    }

    /**
     * Get background.
     */
    public function background(string $color): string
    {
        $now = resolveDate()->now();

        $phase = [
            'day',
            'sunrise',
            'sunset',
        ][($now->hour + $now->year + $now->month + $now->day) % 3];

        return 'data:image/svg+xml;utf8,'.\rawurlencode(resolveViewFactory()->make("exceptions::phases.{$phase}", ['color' => $color])->render());
    }

    /**
     * Get night background.
     */
    public function nightBackground(string $color): string
    {
        return 'data:image/svg+xml;utf8,'.\rawurlencode(resolveViewFactory()->make('exceptions::phases.night', ['color' => $color])->render());
    }

    /**
     * Get illustration.
     */
    public function illustration(string $color, int $status, int $code): string
    {
        return 'data:image/svg+xml;utf8,'.\rawurlencode(resolveViewFactory()->first(["exceptions::illustrations.{$status}", 'exceptions::illustrations.'.\mb_substr((string) $status, 0, -2).'xx', 'exceptions::illustrations.1xx'], ['color' => $color])->render());
    }
}

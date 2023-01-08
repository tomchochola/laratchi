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
        $phase = $this->sunPhase();

        return 'data:image/svg+xml;utf8,'.\rawurlencode(resolveViewFactory()->make("exceptions::phases.{$phase}")->render());
    }

    /**
     * Get night background.
     */
    public function nightBackground(string $color): string
    {
        return 'data:image/svg+xml;utf8,'.\rawurlencode(resolveViewFactory()->make('exceptions::phases.night')->render());
    }

    /**
     * Get illustration.
     */
    public function illustration(string $color): string
    {
        return 'data:image/svg+xml;utf8,'.\rawurlencode(resolveViewFactory()->make('exceptions::illustrations.1xx', ['color' => $color])->render());
    }

    /**
     * Get sun phase.
     *
     * @return 'day'|'sunrise'|'sunset'|'night'
     */
    protected function sunPhase(): string
    {
        $timestamp = resolveDate()->now()->getTimestamp();

        [$lat, $lng] = $this->sunLatLng();

        $sun = \date_sun_info($timestamp, $lat, $lng);

        $sunrise = (int) $sun['sunrise'];
        $sunset = (int) $sun['sunset'];
        $mid = (int) $sun['transit'];

        $riseDuration = ($mid - $sunrise) / 2;

        if ($timestamp >= $sunrise && $timestamp <= $sunrise + $riseDuration) {
            return 'sunrise';
        }

        if ($timestamp >= $sunrise && $timestamp <= $sunset - $riseDuration) {
            return 'day';
        }

        if ($timestamp > $sunset || $timestamp < $sunrise) {
            return 'night';
        }

        return 'sunset';
    }

    /**
     * Get lat, lng for sun phase.
     *
     * @return array{float, float}
     */
    protected function sunLatLng(): array
    {
        return [
            50.075538,
            14.437801,
        ];
    }
}

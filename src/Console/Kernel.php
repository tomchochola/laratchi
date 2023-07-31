<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    public const SCHEDULE_TIMEZONE = 'Europe/Prague';

    /**
     * @inheritDoc
     */
    protected function schedule(Schedule $schedule): void
    {
        parent::schedule($schedule);

        foreach (mustConfigArray('auth.passwords') as $passwordBrokerName => $config) {
            $schedule
                ->command("auth:clear-resets {$passwordBrokerName}")
                ->dailyAt('04:00')
                ->timezone(static::SCHEDULE_TIMEZONE)
                ->runInBackground();
        }
    }
}

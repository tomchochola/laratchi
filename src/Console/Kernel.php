<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * @inheritDoc
     */
    protected function schedule(Schedule $schedule): void
    {
        parent::schedule($schedule);

        $this->authClearResets($schedule, 'UTC');
    }

    /**
     * Run auth:clear-resets for all password brokers.
     */
    protected function authClearResets(Schedule $schedule, string $timezone): void
    {
        foreach (mustConfigArray('auth.passwords') as $passwordBrokerName => $config) {
            $schedule->command("auth:clear-resets {$passwordBrokerName}")->dailyAt('04:00')->timezone($timezone)->withoutOverlapping()->runInBackground();
        }
    }

    /**
     * @inheritDoc
     */
    protected function commands(): void
    {
        parent::commands();

        $this->load(resolveApp()->basePath('app/Console/Commands'));

        require resolveApp()->basePath('routes/console.php');
    }
}

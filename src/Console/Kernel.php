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

        $this->authClearResets($schedule);
    }

    /**
     * Run auth:clear-resets for all password brokers.
     */
    protected function authClearResets(Schedule $schedule): void
    {
        foreach (mustConfigArray('auth.passwords') as $passwordBrokerName => $config) {
            $schedule->command("auth:clear-resets {$passwordBrokerName}")->dailyAt('04:00')->withoutOverlapping()->runInBackground();
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

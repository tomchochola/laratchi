<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Testing;

use Illuminate\Console\Command;
use Illuminate\Notifications\AnonymousNotifiable;

class TestMailCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected $signature = 'test:mail';

    /**
     * @inheritDoc
     */
    protected $description = 'Test mail command';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $mail = \filter_var($this->ask('Mail to send test mail: '), \FILTER_VALIDATE_EMAIL);

        if ($mail === false) {
            $this->error('Given data is not valid e-mail address.');

            return static::FAILURE;
        }

        $target = (new AnonymousNotifiable())->route('mail', $mail);

        \resolveNotificator()->sendNow($target, new TestNotification());

        $this->info("Test notification sent to: [{$mail}].");

        return static::SUCCESS;
    }
}

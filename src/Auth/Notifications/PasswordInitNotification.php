<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue as ShouldQueueContract;
use Illuminate\Notifications\Messages\MailMessage;

class PasswordInitNotification extends ResetPassword implements ShouldQueueContract
{
    use Queueable;

    /**
     * @inheritDoc
     */
    public function __construct(string $token)
    {
        parent::__construct($token);

        $this->afterCommit();
    }

    /**
     * @inheritDoc
     */
    protected function buildMailMessage(mixed $url): MailMessage
    {
        return (new MailMessage())
            ->subject(mustTransJsonString('Init Password Notification'))
            ->line(mustTransJsonString('You are receiving this email because we received a password init request for your account.'))
            ->action(mustTransJsonString('Init Password'), $url)
            ->line(mustTransJsonString('This password init link will expire in :count minutes.', ['en'], ['count' => (string) mustConfigInt('auth.passwords.'.mustConfigString('auth.defaults.passwords').'.expire')]))
            ->line(mustTransJsonString('If you did not request a password init, no further action is required.'));
    }
}

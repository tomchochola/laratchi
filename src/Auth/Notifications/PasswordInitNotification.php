<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Contracts\Queue\ShouldQueue as ShouldQueueContract;
use Illuminate\Notifications\Messages\MailMessage;

class PasswordInitNotification extends ResetPassword implements ShouldQueueContract
{
    use Queueable;

    /**
     * @inheritDoc
     */
    public function __construct(protected ?string $guardName = null, ?string $token = null, protected ?string $spa = null, protected ?string $url = null)
    {
        parent::__construct($token ?? '');

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
            ->line(mustTransJsonString('This password init link will expire in :count minutes.', ['count' => (string) mustConfigInt('auth.passwords.'.mustConfigString('auth.defaults.passwords').'.expire')]))
            ->line(mustTransJsonString('If you did not request a password init, no further action is required.'));
    }

    /**
     * @inheritDoc
     */
    protected function resetUrl(mixed $notifiable): string
    {
        if ($this->url !== null) {
            return $this->url;
        }

        \assert($notifiable instanceof CanResetPasswordContract);

        \assert($this->guardName !== null && $this->token !== null);

        $query = \http_build_query([
            'guard' => $this->guardName,
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        return ($this->spa ?? mustTransString('spa.password_init_url')).'?'.$query;
    }
}

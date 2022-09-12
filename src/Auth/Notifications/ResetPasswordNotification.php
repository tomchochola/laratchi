<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Contracts\Queue\ShouldQueue as ShouldQueueContract;

class ResetPasswordNotification extends ResetPassword implements ShouldQueueContract
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
    protected function resetUrl(mixed $notifiable): string
    {
        \assert($notifiable instanceof CanResetPassword);

        $query = \http_build_query([
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        return mustTransString('spa.password_reset_url').'?'.$query;
    }
}

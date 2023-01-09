<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Contracts\Queue\ShouldQueue as ShouldQueueContract;

class ResetPasswordNotification extends ResetPassword implements ShouldQueueContract
{
    use Queueable;

    /**
     * @inheritDoc
     */
    public function __construct(string $token, protected ?string $spa = null, protected ?string $url = null)
    {
        parent::__construct($token);

        $this->afterCommit();
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

        $query = \http_build_query([
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        return ($this->spa ?? mustTransString('spa.password_reset_url')).'?'.$query;
    }
}

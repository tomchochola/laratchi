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
    public function __construct(protected ?string $guardName = null, ?string $token = null, protected ?string $spa = null, protected ?string $url = null)
    {
        parent::__construct($token ?? '');

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

        \assert($this->guardName !== null && $this->token !== null);

        $query = \http_build_query([
            'guard' => $this->guardName,
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        return ($this->spa ?? mustTransString('spa.password_reset_url')).'?'.$query;
    }
}

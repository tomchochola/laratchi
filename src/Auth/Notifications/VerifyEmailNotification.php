<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Contracts\Queue\ShouldQueue as ShouldQueueContract;
use Tomchochola\Laratchi\Support\SignedUrlSupport;

class VerifyEmailNotification extends VerifyEmail implements ShouldQueueContract
{
    use Queueable;

    /**
     * Create a new notification.
     */
    public function __construct(protected string $guardName, protected ?string $action = null, protected ?string $url = null, protected ?string $spa = null)
    {
        \assert($this->action !== null || $this->url !== null, 'action or url must be provided');

        $this->afterCommit();
    }

    /**
     * Make signed url.
     */
    public function signedUrl(MustVerifyEmailContract&AuthenticatableContract $user): string
    {
        $parameters = [
            'id' => $user->getAuthIdentifier(),
            'email' => $user->getEmailForVerification(),
            'guard' => $this->guardName,
        ];

        $expires = mustConfigInt('auth.verification.expire');

        \assert($this->action !== null);

        return SignedUrlSupport::make($this->action, $parameters, $expires);
    }

    /**
     * @inheritDoc
     */
    protected function verificationUrl(mixed $notifiable): string
    {
        if ($this->url !== null) {
            return $this->url;
        }

        \assert($notifiable instanceof MustVerifyEmailContract);
        \assert($notifiable instanceof AuthenticatableContract);

        $signedUrl = $this->signedUrl($notifiable);

        $query = \http_build_query([
            'url' => $signedUrl,
        ]);

        return ($this->spa ?? mustTransString('spa.email_verification_verify_url')).'?'.$query;
    }
}

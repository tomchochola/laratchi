<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Queue\ShouldQueue as ShouldQueueContract;
use Tomchochola\Laratchi\Auth\Http\Controllers\EmailVerificationVerifyController;
use Tomchochola\Laratchi\Support\SignedUrlSupport;

class VerifyEmailNotification extends VerifyEmail implements ShouldQueueContract
{
    use Queueable;

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        $this->afterCommit();
    }

    /**
     * @inheritDoc
     */
    protected function verificationUrl(mixed $notifiable): string
    {
        \assert($notifiable instanceof MustVerifyEmail);
        \assert($notifiable instanceof Authenticatable);

        $parameters = [
            'id' => $notifiable->getAuthIdentifier(),
            'hash' => \hash('sha256', $notifiable->getEmailForVerification()),
        ];

        $expires = mustConfigInt('auth.verification.expire');

        $signedUrl = SignedUrlSupport::make(inject(EmailVerificationVerifyController::class)::class, $parameters, $expires);

        $query = \http_build_query([
            'url' => $signedUrl,
        ]);

        return mustTransString('spa.email_verification_verify_url').'?'.$query;
    }
}

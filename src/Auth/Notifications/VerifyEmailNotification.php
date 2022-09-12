<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Queue\ShouldQueue as ShouldQueueContract;
use Illuminate\Support\Carbon;
use Tomchochola\Laratchi\Auth\Http\Controllers\EmailVerificationVerifyController;

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
            'expires' => Carbon::now()->addMinutes(mustConfigInt('auth.verification.expire'))->getTimestamp(),
        ];

        \ksort($parameters);

        $controllers = inject(EmailVerificationVerifyController::class)::class;

        $signedUrl = resolveUrlFactory()->action($controllers, \array_merge($parameters, [
            'signature' => \hash_hmac('sha256', resolveUrlFactory()->action($controllers, $parameters), mustConfigString('app.key')),
        ]));

        $query = \http_build_query([
            'url' => $signedUrl,
        ]);

        return mustTransString('spa.email_verification_verify_url').'?'.$query;
    }
}

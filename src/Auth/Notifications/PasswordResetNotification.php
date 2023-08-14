<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue as ShouldQueueContract;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Tomchochola\Laratchi\Config\Config;
use Tomchochola\Laratchi\Translation\Trans;

class PasswordResetNotification extends Notification implements ShouldQueueContract
{
    use Queueable;

    /**
     * Template.
     *
     * @var class-string<self>
     */
    public static string $template = self::class;

    /**
     * Trans.
     */
    public Trans $trans;

    /**
     * Config.
     */
    public Config $config;

    /**
     * Create a new notification instance.
     */
    protected function __construct(
        protected string $guardName,
        protected ?string $token = null,
        protected ?string $email = null,
        protected ?string $spa = null,
        protected ?string $url = null,
    ) {
        $this->afterCommit();

        $this->trans = new Trans();
        $this->config = new Config();
    }

    /**
     * Inject.
     */
    public static function inject(string $guardName, ?string $token = null, ?string $email = null, ?string $spa = null, ?string $url = null): self
    {
        return new static::$template($guardName, $token, $email, $spa, $url);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject($this->trans->assertString('Reset Password Notification'))
            ->line($this->trans->assertString('You are receiving this email because we received a password reset request for your account.'))
            ->action($this->trans->assertString('Reset Password'), $this->getUrl($notifiable))
            ->line(
                $this->trans->assertString('This password reset link will expire in :count minutes.', [
                    'count' => (string) $this->config->assertInt("auth.passwords.{$this->guardName}.expire"),
                ]),
            )
            ->line($this->trans->assertString('If you did not request a password reset, no further action is required.'));
    }

    /**
     * Get url.
     */
    protected function getUrl(mixed $notifiable): string
    {
        if ($this->url !== null) {
            return $this->url;
        }

        \assert($this->token !== null && $this->email !== null && $this->locale !== null);

        $query = \http_build_query([
            'guard' => $this->guardName,
            'token' => $this->token,
            'email' => $this->email,
            'locale' => $this->locale,
        ]);

        return ($this->spa ?? $this->trans->assertString('spa.password_reset_url')).'?'.$query;
    }
}

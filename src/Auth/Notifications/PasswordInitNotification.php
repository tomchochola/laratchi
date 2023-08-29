<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue as ShouldQueueContract;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Tomchochola\Laratchi\Config\Config;
use Tomchochola\Laratchi\Support\Typer;
use Tomchochola\Laratchi\Translation\Trans;

class PasswordInitNotification extends Notification implements ShouldQueueContract
{
    use Queueable;

    /**
     * Template.
     *
     * @var class-string<self>
     */
    public static string $template = self::class;

    /**
     * Create a new notification instance.
     */
    protected function __construct(
        protected string $guardName,
        protected string|null $token = null,
        protected string|null $email = null,
        protected string|null $spa = null,
        protected string|null $url = null,
    ) {
        $this->afterCommit();
    }

    /**
     * Inject.
     */
    public static function inject(string $guardName, string|null $token = null, string|null $email = null, string|null $spa = null, string|null $url = null): self
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
        $trans = Trans::inject();
        $config = Config::inject();

        return (new MailMessage())
            ->subject($trans->assertString('Init Password Notification'))
            ->line($trans->assertString('You are receiving this email because we received a password init request for your account.'))
            ->action($trans->assertString('Init Password'), $this->getUrl($notifiable))
            ->line(
                $trans->assertString('This password init link will expire in :count minutes.', [
                    'count' => (string) $config->assertInt("auth.passwords.{$this->guardName}.expire"),
                ]),
            )
            ->line($trans->assertString('If you did not request a password init, no further action is required.'));
    }

    /**
     * Get url.
     */
    protected function getUrl(mixed $notifiable): string
    {
        if ($this->url !== null) {
            return $this->url;
        }

        Typer::assertTrue($this->token !== null && $this->email !== null && $this->locale !== null);

        $query = \http_build_query([
            'guard' => $this->guardName,
            'token' => $this->token,
            'email' => $this->email,
            'locale' => $this->locale,
        ]);

        return ($this->spa ?? Trans::inject()->assertString('spa.password_init_url')) . '?' . $query;
    }
}

<?php

declare(strict_types=1);

namespace Tomchochola\Laratchi\Auth\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue as ShouldQueueContract;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailConfirmationNotification extends Notification implements ShouldQueueContract
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
    protected function __construct(protected string $guardName, protected string $token, protected string $email)
    {
        $this->afterCommit();
    }

    /**
     * Inject.
     */
    public static function inject(string $guardName, string $token, string $email): self
    {
        return new static::$template($guardName, $token, $email);
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
            ->subject(mustTransJsonString('Verify Email Address'))
            ->line(mustTransJsonString('Verification code ":code".', ['code' => $this->token]))
            ->line(mustTransJsonString('If you did not create an account, no further action is required.'));
    }
}

<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Generic internal alert to the owner's notification address. */
class AdminAlert extends Notification implements ShouldQueue
{
    use Queueable;

    /** @param list<string> $lines */
    public function __construct(public string $subject, public array $lines, public ?string $url = null) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)->subject($this->subject)->greeting($this->subject);
        foreach ($this->lines as $line) {
            $mail->line($line);
        }

        return $this->url ? $mail->action('Open in dashboard', $this->url) : $mail;
    }
}

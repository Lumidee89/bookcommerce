<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Acknowledgement sent to people who submit a contact enquiry or speaking invitation. */
class SubmissionReceived extends Notification implements ShouldQueue
{
    use Queueable;

    /** @param list<string> $lines */
    public function __construct(public string $subject, public string $name, public string $reference, public array $lines) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)->subject($this->subject)->greeting("Dear {$this->name},");
        foreach ($this->lines as $line) {
            $mail->line($line);
        }

        return $mail->line("Your reference is **{$this->reference}**. Please quote it if you need to follow up.");
    }
}

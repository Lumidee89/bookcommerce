<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Booking $booking) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $b = $this->booking;
        $when = $b->starts_at->copy()->setTimezone($b->client_timezone)->format('l j F Y, g:i a').' ('.$b->client_timezone.')';

        $mail = (new MailMessage)
            ->subject("Reminder: {$b->service_name}")
            ->greeting("Dear {$b->name},")
            ->line("This is a reminder of your consultation on {$when}.");

        if ($b->meeting_url) {
            $mail->line("Meeting link: {$b->meeting_url}");
        }
        if ($b->meeting_details) {
            $mail->line($b->meeting_details);
        }

        return $mail->action('Manage your booking', $b->manageUrl());
    }
}

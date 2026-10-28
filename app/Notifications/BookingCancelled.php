<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingCancelled extends Notification implements ShouldQueue
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

        $mail = (new MailMessage)
            ->subject("Cancelled: {$b->service_name}")
            ->greeting("Dear {$b->name},")
            ->line("Your consultation booking {$b->reference} has been cancelled.");

        if ($b->fee > 0) {
            $mail->line('Any refund due will be handled according to the published cancellation policy. We will contact you if anything further is needed.');
        }

        return $mail->action('Book another time', route('consultations.index'));
    }
}

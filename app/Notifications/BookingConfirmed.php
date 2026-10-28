<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Support\Ics;
use App\Support\Money;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingConfirmed extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Booking $booking, public bool $rescheduled = false) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $b = $this->booking;
        $when = $b->starts_at->copy()->setTimezone($b->client_timezone)->format('l j F Y, g:i a').' ('.$b->client_timezone.')';

        $mail = (new MailMessage)
            ->subject(($this->rescheduled ? 'Rescheduled: ' : 'Confirmed: ').$b->service_name)
            ->greeting("Dear {$b->name},")
            ->line($this->rescheduled ? 'Your consultation has been rescheduled.' : 'Your consultation is confirmed.')
            ->line("**{$b->service_name}**")
            ->line("When: {$when}")
            ->line("Reference: {$b->reference}");

        if ($b->fee > 0) {
            $mail->line('Fee paid: '.Money::format($b->fee, $b->currency));
        }

        $mail->line($b->meeting_url
            ? "Meeting link: {$b->meeting_url}"
            : 'Meeting details will be sent to you before the session.');

        if ($instructions = $b->service?->preparation_instructions) {
            $mail->line("**How to prepare:** {$instructions}");
        }

        return $mail
            ->action('Manage your booking', $b->manageUrl())
            ->line('A calendar invitation is attached.')
            ->attachData(Ics::forBooking($b), 'consultation.ics', ['mime' => 'text/calendar; charset=UTF-8; method=REQUEST']);
    }
}

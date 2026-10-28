<?php

namespace App\Support;

use App\Models\Booking;

class Ics
{
    public static function forBooking(Booking $booking): string
    {
        $fmt = fn ($dt) => $dt->copy()->utc()->format('Ymd\THis\Z');
        $escape = fn (?string $v) => str_replace(['\\', ';', ',', "\n"], ['\\\\', '\;', '\,', '\n'], (string) $v);

        $description = trim(implode("\n", array_filter([
            "Booking reference: {$booking->reference}",
            $booking->meeting_url ? "Join: {$booking->meeting_url}" : null,
            $booking->meeting_details,
            'Manage your booking: '.$booking->manageUrl(),
        ])));

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Yemi Adebanjo//Consultations//EN',
            'METHOD:REQUEST',
            'BEGIN:VEVENT',
            'UID:'.$booking->reference.'@'.parse_url(config('app.url'), PHP_URL_HOST),
            'DTSTAMP:'.$fmt(now()),
            'DTSTART:'.$fmt($booking->starts_at),
            'DTEND:'.$fmt($booking->ends_at),
            'SUMMARY:'.$escape("Consultation: {$booking->service_name}"),
            'DESCRIPTION:'.$escape($description),
            'LOCATION:'.$escape($booking->meeting_url ?: ($booking->delivery_method === 'in_person' ? 'Details to follow' : 'Online')),
            'STATUS:CONFIRMED',
            'END:VEVENT',
            'END:VCALENDAR',
        ];

        return implode("\r\n", $lines)."\r\n";
    }
}

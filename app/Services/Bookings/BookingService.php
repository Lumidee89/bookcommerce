<?php

namespace App\Services\Bookings;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\ConsultationService;
use App\Notifications\AdminAlert;
use App\Notifications\BookingCancelled;
use App\Notifications\BookingConfirmed;
use App\Notifications\BookingReminder;
use App\Support\AdminRecipient;
use Carbon\CarbonImmutable;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class BookingService
{
    public function __construct(private readonly AvailabilityService $availability) {}

    /**
     * Create a booking after re-validating availability under a calendar-wide lock.
     * Paid services get a temporary hold; free services are confirmed immediately (BR-007, BR-008).
     *
     * @param  array<string, mixed>  $client
     */
    public function create(ConsultationService $service, CarbonImmutable $startUtc, array $client): Booking
    {
        $booking = $this->withCalendarLock(function () use ($service, $startUtc, $client) {
            $this->expireHolds();

            if (! $this->availability->isAvailable($service, $startUtc)) {
                throw new SlotUnavailableException;
            }

            $paid = $service->requiresPayment();

            return Booking::create([
                'reference' => $this->newReference(),
                'manage_token' => Str::random(40),
                'consultation_service_id' => $service->id,
                'service_name' => $service->name,
                'starts_at' => $startUtc,
                'ends_at' => $startUtc->addMinutes($service->duration_minutes),
                'client_timezone' => $client['timezone'],
                'name' => $client['name'],
                'email' => strtolower($client['email']),
                'phone' => $client['phone'] ?? null,
                'organisation' => $client['organisation'] ?? null,
                'needs' => $client['needs'] ?? null,
                'fee' => $service->fee,
                'currency' => $service->currency,
                'delivery_method' => $service->delivery_method->value,
                'status' => $paid ? BookingStatus::PendingPayment : BookingStatus::Confirmed,
                'hold_expires_at' => $paid ? now()->addMinutes(config('platform.booking.hold_minutes')) : null,
                'confirmed_at' => $paid ? null : now(),
                'slot_lock' => $this->slotKey($startUtc),
            ]);
        });

        $booking->statusHistory()->create(['to_status' => $booking->status->value, 'note' => 'Booked online', 'created_at' => now()]);

        if ($booking->status === BookingStatus::Confirmed) {
            $this->notifyConfirmed($booking);
        }

        return $booking;
    }

    /** Called once the payment for a held booking is verified. */
    public function markPaid(Booking $booking): void
    {
        $booking->refresh();
        if (! in_array($booking->status, [BookingStatus::PendingPayment, BookingStatus::Expired], true)) {
            return;
        }

        $confirmed = false;
        try {
            $this->withCalendarLock(function () use ($booking, &$confirmed) {
                $start = CarbonImmutable::parse($booking->starts_at);
                if (! $this->availability->hasConflict($start, CarbonImmutable::parse($booking->ends_at), $booking->id)) {
                    $booking->slot_lock = $this->slotKey($start);
                    $booking->transitionTo(BookingStatus::Confirmed, 'Payment verified');
                    $confirmed = true;
                }
            });
        } catch (SlotUnavailableException) {
            // Another booking holds this exact slot; handled below.
        }

        if ($confirmed) {
            $this->notifyConfirmed($booking);

            return;
        }

        // Paid after the hold lapsed and someone else took the slot: a person must reschedule or refund.
        $booking->transitionTo(BookingStatus::RequiresAttention, 'Payment received after hold expired; slot no longer free');
        AdminRecipient::notify(new AdminAlert(
            "Booking {$booking->reference} needs attention",
            ['Payment was received after the reservation expired and the slot has since been taken.', 'Please reschedule or refund the client.'],
            route('admin.bookings.show', $booking),
        ));
    }

    public function cancel(Booking $booking, ?string $reason, bool $byClient, ?int $userId = null): void
    {
        if (! in_array($booking->status, [BookingStatus::Confirmed, BookingStatus::PendingPayment, BookingStatus::RequiresAttention], true)) {
            throw new BookingPolicyException('This booking can no longer be cancelled.');
        }

        if ($byClient && $booking->status === BookingStatus::Confirmed && ! $this->clientMayChange($booking)) {
            throw new BookingPolicyException("Bookings can only be cancelled online at least {$this->availability->settings()->changeNoticeHours} hours before the start time. Please contact us.");
        }

        $booking->cancellation_reason = $reason;
        $booking->transitionTo(BookingStatus::Cancelled, $byClient ? 'Cancelled by client' : 'Cancelled by administrator', $userId);

        Notification::route('mail', $booking->email)->notify(new BookingCancelled($booking));
        if ($byClient) {
            AdminRecipient::notify(new AdminAlert("Booking {$booking->reference} cancelled by client", [$reason ?: 'No reason given.'], route('admin.bookings.show', $booking)));
        }
    }

    public function reschedule(Booking $booking, CarbonImmutable $newStartUtc, bool $byClient, ?int $userId = null): void
    {
        if (! in_array($booking->status, [BookingStatus::Confirmed, BookingStatus::RequiresAttention], true)) {
            throw new BookingPolicyException('Only confirmed bookings can be rescheduled.');
        }

        $settings = $this->availability->settings();
        if ($byClient && (! $this->clientMayChange($booking) || $booking->reschedule_count >= $settings->maxReschedules)) {
            throw new BookingPolicyException('This booking can no longer be rescheduled online. Please contact us.');
        }

        $this->withCalendarLock(function () use ($booking, $newStartUtc, $byClient, $userId) {
            if (! $booking->service || ! $this->availability->isAvailable($booking->service, $newStartUtc, $booking->id)) {
                throw new SlotUnavailableException;
            }

            $old = $booking->starts_at->toIso8601String();
            $booking->fill([
                'starts_at' => $newStartUtc,
                'ends_at' => $newStartUtc->addMinutes($booking->service->duration_minutes),
                'slot_lock' => $this->slotKey($newStartUtc),
                'reschedule_count' => $booking->reschedule_count + 1,
                'reminder_sent_at' => null,
            ]);
            $booking->transitionTo(BookingStatus::Confirmed, "Rescheduled from {$old}".($byClient ? ' by client' : ''), $userId);
        });

        $this->notifyConfirmed($booking, rescheduled: true);
    }

    public function clientMayChange(Booking $booking): bool
    {
        return $booking->starts_at->gt(now()->addHours($this->availability->settings()->changeNoticeHours));
    }

    /** Release unpaid holds whose time has run out. */
    public function expireHolds(): int
    {
        $expired = Booking::where('status', BookingStatus::PendingPayment)->where('hold_expires_at', '<=', now())->get();
        foreach ($expired as $booking) {
            $booking->transitionTo(BookingStatus::Expired, 'Payment not completed before the hold expired');
        }

        return $expired->count();
    }

    public function sendReminders(): int
    {
        $due = Booking::where('status', BookingStatus::Confirmed)
            ->whereNull('reminder_sent_at')
            ->whereBetween('starts_at', [now(), now()->addHours(24)])
            ->get();

        foreach ($due as $booking) {
            Notification::route('mail', $booking->email)->notify(new BookingReminder($booking));
            $booking->update(['reminder_sent_at' => now()]);
        }

        return $due->count();
    }

    private function notifyConfirmed(Booking $booking, bool $rescheduled = false): void
    {
        Notification::route('mail', [$booking->email => $booking->name])->notify(new BookingConfirmed($booking, $rescheduled));
        AdminRecipient::notify(new AdminAlert(
            ($rescheduled ? 'Booking rescheduled: ' : 'New consultation booked: ').$booking->service_name,
            ["{$booking->name} — ".$booking->starts_at->copy()->setTimezone(config('platform.timezone'))->format('l j F Y, g:i a T')],
            route('admin.bookings.show', $booking),
        ));
    }

    private function withCalendarLock(callable $callback): mixed
    {
        return Cache::lock('booking-calendar', 15)->block(10, function () use ($callback) {
            try {
                return DB::transaction($callback);
            } catch (UniqueConstraintViolationException) {
                // The slot_lock unique index caught a race the availability check missed.
                throw new SlotUnavailableException;
            }
        });
    }

    private function slotKey(CarbonImmutable $startUtc): string
    {
        return $startUtc->utc()->format('Y-m-d H:i');
    }

    private function newReference(): string
    {
        do {
            $reference = 'BK-'.strtoupper(Str::random(7));
        } while (Booking::where('reference', $reference)->exists());

        return $reference;
    }
}

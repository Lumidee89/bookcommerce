<?php

namespace App\Services\Bookings;

use App\Models\AvailabilityException;
use App\Models\AvailabilityRule;
use App\Models\Booking;
use App\Models\ConsultationService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Computes bookable slots from working hours, exceptions and existing bookings.
 * All calculations happen in the owner's time zone; results are returned in UTC.
 */
class AvailabilityService
{
    public function __construct(private ?BookingSettings $settings = null)
    {
        $this->settings ??= BookingSettings::current();
    }

    public function settings(): BookingSettings
    {
        return $this->settings;
    }

    /**
     * @return list<CarbonImmutable> UTC start times
     */
    public function slotsForDate(ConsultationService $service, string $date, ?int $ignoreBookingId = null): array
    {
        $day = CarbonImmutable::parse($date, $this->settings->timezone)->startOfDay();

        return $this->slotsForDay($service, $day, $this->busyBetween($day, $day->endOfDay(), $ignoreBookingId));
    }

    /**
     * @return list<string> local dates (Y-m-d) in the month that have at least one free slot
     */
    public function availableDatesInMonth(ConsultationService $service, string $month): array
    {
        $start = CarbonImmutable::parse($month.'-01', $this->settings->timezone)->startOfMonth();
        $end = $start->endOfMonth();
        $busy = $this->busyBetween($start, $end);

        $dates = [];
        for ($day = $start; $day->lte($end); $day = $day->addDay()) {
            if ($this->slotsForDay($service, $day, $busy) !== []) {
                $dates[] = $day->toDateString();
            }
        }

        return $dates;
    }

    public function isAvailable(ConsultationService $service, CarbonImmutable $startUtc, ?int $ignoreBookingId = null): bool
    {
        $localDate = $startUtc->setTimezone($this->settings->timezone)->toDateString();

        foreach ($this->slotsForDate($service, $localDate, $ignoreBookingId) as $slot) {
            if ($slot->equalTo($startUtc)) {
                return true;
            }
        }

        return false;
    }

    /** Whether an exact time range collides with another active booking (ignores notice/working-hour rules). */
    public function hasConflict(CarbonImmutable $startUtc, CarbonImmutable $endUtc, ?int $ignoreBookingId = null): bool
    {
        return $this->busyBetween($startUtc, $endUtc, $ignoreBookingId)->isNotEmpty();
    }

    /**
     * @param  Collection<int, Booking>  $busy
     * @return list<CarbonImmutable>
     */
    private function slotsForDay(ConsultationService $service, CarbonImmutable $day, Collection $busy): array
    {
        $s = $this->settings;
        $now = CarbonImmutable::now();
        $earliest = $now->addHours($s->minNoticeHours);
        $latest = $now->addDays($s->maxAdvanceDays)->endOfDay();

        if ($day->endOfDay()->lt($earliest) || $day->gt($latest)) {
            return [];
        }

        $dayBusy = $busy->filter(fn (Booking $b) => $b->starts_at->copy()->setTimezone($s->timezone)->isSameDay($day));
        if ($s->maxPerDay > 0 && $dayBusy->count() >= $s->maxPerDay) {
            return [];
        }

        $slots = [];
        foreach ($this->windowsFor($day) as [$windowStart, $windowEnd]) {
            for ($start = $windowStart; $start->addMinutes($service->duration_minutes)->lte($windowEnd); $start = $start->addMinutes($s->slotInterval)) {
                $end = $start->addMinutes($service->duration_minutes);
                if ($start->lt($earliest) || $start->gt($latest)) {
                    continue;
                }

                $clashes = $busy->contains(fn (Booking $b) => $start->lt($b->ends_at->copy()->addMinutes($s->bufferMinutes))
                    && $end->gt($b->starts_at->copy()->subMinutes($s->bufferMinutes)));

                if (! $clashes) {
                    $slots[] = $start->utc();
                }
            }
        }

        return $slots;
    }

    /**
     * @return list<array{0: CarbonImmutable, 1: CarbonImmutable}>
     */
    private function windowsFor(CarbonImmutable $day): array
    {
        $exceptions = AvailabilityException::whereDate('date', $day->toDateString())->get();

        if ($exceptions->contains(fn (AvailabilityException $e) => $e->blocksWholeDay())) {
            return [];
        }

        $ranges = $exceptions->isNotEmpty()
            ? $exceptions->map(fn ($e) => [$e->start_time, $e->end_time])
            : AvailabilityRule::where('is_active', true)->where('day_of_week', $day->dayOfWeek)->orderBy('start_time')->get()
                ->map(fn ($r) => [$r->start_time, $r->end_time]);

        return $ranges->map(fn ($r) => [
            $day->setTimeFromTimeString($r[0]),
            $day->setTimeFromTimeString($r[1]),
        ])->filter(fn ($w) => $w[0]->lt($w[1]))->values()->all();
    }

    /**
     * @return Collection<int, Booking>
     */
    private function busyBetween(CarbonImmutable $from, CarbonImmutable $to, ?int $ignoreBookingId = null): Collection
    {
        $pad = $this->settings->bufferMinutes;

        return Booking::occupying()
            ->where('starts_at', '<', $to->utc()->addMinutes($pad))
            ->where('ends_at', '>', $from->utc()->subMinutes($pad))
            ->when($ignoreBookingId, fn ($q) => $q->whereKeyNot($ignoreBookingId))
            ->get(['id', 'starts_at', 'ends_at']);
    }
}

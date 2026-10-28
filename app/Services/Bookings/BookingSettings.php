<?php

namespace App\Services\Bookings;

use App\Models\Setting;

final class BookingSettings
{
    public function __construct(
        public readonly string $timezone,
        public readonly int $slotInterval,
        public readonly int $bufferMinutes,
        public readonly int $minNoticeHours,
        public readonly int $maxAdvanceDays,
        public readonly int $maxPerDay,
        public readonly int $changeNoticeHours,
        public readonly int $maxReschedules,
    ) {}

    public static function current(): self
    {
        return new self(
            timezone: config('platform.timezone'),
            slotInterval: max(5, (int) Setting::get('booking.slot_interval_minutes', 30)),
            bufferMinutes: (int) Setting::get('booking.buffer_minutes', 15),
            minNoticeHours: (int) Setting::get('booking.min_notice_hours', 24),
            maxAdvanceDays: (int) Setting::get('booking.max_advance_days', 60),
            maxPerDay: (int) Setting::get('booking.max_per_day', 4),
            changeNoticeHours: (int) Setting::get('booking.change_notice_hours', 24),
            maxReschedules: (int) Setting::get('booking.max_reschedules', 2),
        );
    }

    public static function defaults(): array
    {
        return [
            'booking.slot_interval_minutes' => 30,
            'booking.buffer_minutes' => 15,
            'booking.min_notice_hours' => 24,
            'booking.max_advance_days' => 60,
            'booking.max_per_day' => 4,
            'booking.change_notice_hours' => 24,
            'booking.max_reschedules' => 2,
        ];
    }
}

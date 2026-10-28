<?php

namespace App\Enums;

enum EventStatus: string
{
    use HasOptions;

    case Scheduled = 'scheduled';
    case Postponed = 'postponed';
    case Cancelled = 'cancelled';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Scheduled => 'Scheduled',
            self::Postponed => 'Postponed',
            self::Cancelled => 'Cancelled',
            self::Completed => 'Completed',
        };
    }
}

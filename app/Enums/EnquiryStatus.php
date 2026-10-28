<?php

namespace App\Enums;

enum EnquiryStatus: string
{
    use HasOptions;

    case New = 'new';
    case InProgress = 'in_progress';
    case AwaitingResponse = 'awaiting_response';
    case Resolved = 'resolved';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::InProgress => 'In progress',
            self::AwaitingResponse => 'Awaiting response',
            self::Resolved => 'Resolved',
            self::Closed => 'Closed',
        };
    }
}

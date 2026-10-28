<?php

namespace App\Enums;

enum SalesStatus: string
{
    use HasOptions;

    case Available = 'available';
    case Free = 'free';
    case Forthcoming = 'forthcoming';
    case DisplayOnly = 'display_only';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Available for purchase',
            self::Free => 'Free',
            self::Forthcoming => 'Forthcoming',
            self::DisplayOnly => 'Display only',
        };
    }
}

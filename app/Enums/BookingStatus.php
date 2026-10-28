<?php

namespace App\Enums;

enum BookingStatus: string
{
    use HasOptions;

    case PendingPayment = 'pending_payment';
    case Confirmed = 'confirmed';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case NoShow = 'no_show';
    case Expired = 'expired';
    case RequiresAttention = 'requires_attention';

    public function label(): string
    {
        return match ($this) {
            self::PendingPayment => 'Pending payment',
            self::Confirmed => 'Confirmed',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
            self::NoShow => 'No show',
            self::Expired => 'Expired (unpaid)',
            self::RequiresAttention => 'Requires attention',
        };
    }
}

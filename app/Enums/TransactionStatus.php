<?php

namespace App\Enums;

enum TransactionStatus: string
{
    use HasOptions;

    case Pending = 'pending';
    case Success = 'success';
    case Failed = 'failed';
    case Abandoned = 'abandoned';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Success => 'Successful',
            self::Failed => 'Failed',
            self::Abandoned => 'Abandoned',
            self::Refunded => 'Refunded',
        };
    }
}

<?php

namespace App\Services\Bookings;

use RuntimeException;

class SlotUnavailableException extends RuntimeException
{
    public function __construct(string $message = 'Sorry, that time is no longer available. Please choose another slot.')
    {
        parent::__construct($message);
    }
}

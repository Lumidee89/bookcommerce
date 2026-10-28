<?php

namespace App\Enums;

enum DeliveryMethod: string
{
    use HasOptions;

    case Online = 'online';
    case InPerson = 'in_person';
    case Phone = 'phone';
    case Hybrid = 'hybrid';

    public function label(): string
    {
        return match ($this) {
            self::Online => 'Online video call',
            self::InPerson => 'In person',
            self::Phone => 'Telephone',
            self::Hybrid => 'Online or in person',
        };
    }
}

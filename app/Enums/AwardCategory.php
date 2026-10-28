<?php

namespace App\Enums;

enum AwardCategory: string
{
    use HasOptions;

    case Award = 'award';
    case Qualification = 'qualification';
    case Certification = 'certification';
    case Membership = 'membership';
    case Achievement = 'achievement';

    public function label(): string
    {
        return match ($this) {
            self::Award => 'Awards and honours',
            self::Qualification => 'Academic qualifications',
            self::Certification => 'Professional certifications',
            self::Membership => 'Professional memberships',
            self::Achievement => 'Other verified achievements',
        };
    }
}

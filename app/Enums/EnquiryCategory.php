<?php

namespace App\Enums;

enum EnquiryCategory: string
{
    use HasOptions;

    case Books = 'books';
    case Consultations = 'consultations';
    case Speaking = 'speaking';
    case Media = 'media';
    case Partnerships = 'partnerships';
    case General = 'general';

    public function label(): string
    {
        return match ($this) {
            self::Books => 'Books',
            self::Consultations => 'Consultations',
            self::Speaking => 'Speaking',
            self::Media => 'Media',
            self::Partnerships => 'Partnerships',
            self::General => 'General enquiry',
        };
    }
}

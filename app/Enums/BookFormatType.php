<?php

namespace App\Enums;

enum BookFormatType: string
{
    use HasOptions;

    case Pdf = 'pdf';
    case Epub = 'epub';
    case Paperback = 'paperback';
    case Hardcover = 'hardcover';

    public function label(): string
    {
        return match ($this) {
            self::Pdf => 'PDF e-book',
            self::Epub => 'EPUB e-book',
            self::Paperback => 'Paperback',
            self::Hardcover => 'Hardcover',
        };
    }
}

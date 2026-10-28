<?php

namespace App\Enums;

enum PublicationStatus: string
{
    use HasOptions;

    case Draft = 'draft';
    case Published = 'published';
    case Unpublished = 'unpublished';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Published => 'Published',
            self::Unpublished => 'Unpublished',
            self::Archived => 'Archived',
        };
    }
}

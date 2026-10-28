<?php

namespace App\Support;

use Illuminate\Support\Str;

class Seo
{
    /**
     * Page metadata rendered server-side in the root template so crawlers see it without JavaScript.
     */
    public static function make(?string $title = null, ?string $description = null, ?string $image = null, string $type = 'website', ?array $jsonLd = null): array
    {
        return [
            'title' => $title,
            'description' => $description ? Str::limit(strip_tags($description), 160) : null,
            'image' => $image ?: asset('images/brand/logo-original.jpg'),
            'type' => $type,
            'canonical' => url()->current(),
            'json_ld' => $jsonLd,
        ];
    }
}

<?php

namespace App\Support;

use Illuminate\Support\Str;

class Markdown
{
    /**
     * Render admin-authored Markdown to HTML with raw HTML stripped, so stored content cannot inject scripts.
     */
    public static function render(?string $markdown): string
    {
        if (blank($markdown)) {
            return '';
        }

        return (string) Str::markdown($markdown, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }
}

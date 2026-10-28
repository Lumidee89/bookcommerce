<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Storage;

trait HasPublicUrls
{
    protected static function publicUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (str_starts_with($path, 'http') || str_starts_with($path, '/')) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }
}

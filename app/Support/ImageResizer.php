<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Creates downscaled copies of uploaded images with GD so galleries don't ship full-size originals.
 */
class ImageResizer
{
    public static function thumbnail(string $path, string $disk, int $maxWidth): ?string
    {
        if (! function_exists('imagecreatefromstring')) {
            return null;
        }

        try {
            $source = @imagecreatefromstring(Storage::disk($disk)->get($path));
            if (! $source) {
                return null;
            }

            [$w, $h] = [imagesx($source), imagesy($source)];
            if ($w <= $maxWidth) {
                imagedestroy($source);

                return null;
            }

            $newH = (int) round($h * $maxWidth / $w);
            $thumb = imagecreatetruecolor($maxWidth, $newH);
            imagecopyresampled($thumb, $source, 0, 0, 0, 0, $maxWidth, $newH, $w, $h);

            ob_start();
            imagejpeg($thumb, null, 82);
            $data = ob_get_clean();
            imagedestroy($source);
            imagedestroy($thumb);

            $thumbPath = preg_replace('/\.[^.]+$/', '', $path)."-{$maxWidth}w.jpg";
            Storage::disk($disk)->put($thumbPath, $data);

            return $thumbPath;
        } catch (Throwable) {
            return null;
        }
    }
}

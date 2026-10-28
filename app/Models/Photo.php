<?php

namespace App\Models;

use App\Models\Concerns\HasPublicUrls;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Photo extends Model
{
    use HasPublicUrls;

    protected $guarded = ['id'];

    protected $casts = ['allow_download' => 'boolean'];

    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class);
    }

    public function url(): ?string
    {
        return self::publicUrl($this->path);
    }

    public function thumbUrl(): ?string
    {
        return self::publicUrl($this->thumb_path ?: $this->path);
    }

    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'url' => $this->url(),
            'thumb_url' => $this->thumbUrl(),
            'caption' => $this->caption,
            'credit' => $this->credit,
            'alt' => $this->alt_text ?: ($this->caption ?: 'Photograph'),
            'allow_download' => $this->allow_download,
        ];
    }
}

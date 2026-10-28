<?php

namespace App\Models;

use App\Models\Concerns\HasPublicUrls;
use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Video extends Model
{
    use HasPublicUrls, HasSlug;

    protected $guarded = ['id'];

    protected $casts = [
        'published_at' => 'datetime',
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public static function booted(): void
    {
        static::saving(function (Video $video) {
            [$video->provider, $video->provider_video_id] = self::parseUrl($video->url);
        });
    }

    /**
     * @return array{0: ?string, 1: ?string}
     */
    public static function parseUrl(?string $url): array
    {
        if (! $url) {
            return [null, null];
        }

        if (preg_match('~(?:youtube\.com/(?:watch\?v=|embed/|shorts/|live/)|youtu\.be/)([A-Za-z0-9_-]{11})~', $url, $m)) {
            return ['youtube', $m[1]];
        }

        if (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $url, $m)) {
            return ['vimeo', $m[1]];
        }

        return [null, null];
    }

    public function scopeLive(Builder $query): void
    {
        $query->where('is_published', true)->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(VideoCategory::class, 'video_category_id');
    }

    public function embedUrl(): ?string
    {
        // No autoplay with sound (PRD 5.8).
        return match ($this->provider) {
            'youtube' => "https://www.youtube-nocookie.com/embed/{$this->provider_video_id}?rel=0",
            'vimeo' => "https://player.vimeo.com/video/{$this->provider_video_id}?dnt=1",
            default => null,
        };
    }

    public function thumbnailUrl(): ?string
    {
        if ($this->thumbnail_path) {
            return self::publicUrl($this->thumbnail_path);
        }

        return $this->provider === 'youtube' ? "https://i.ytimg.com/vi/{$this->provider_video_id}/hqdefault.jpg" : null;
    }

    public function toCardArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'thumbnail_url' => $this->thumbnailUrl(),
            'duration' => $this->duration,
            'published_at' => $this->published_at?->toIso8601String(),
            'category' => $this->category ? ['name' => $this->category->name, 'slug' => $this->category->slug] : null,
        ];
    }

    public function toDetailArray(): array
    {
        return $this->toCardArray() + [
            'embed_url' => $this->embedUrl(),
            'url' => $this->url,
            'transcript' => $this->transcript,
        ];
    }
}

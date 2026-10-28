<?php

namespace App\Models;

use App\Enums\EventStatus;
use App\Models\Concerns\HasPublicUrls;
use App\Models\Concerns\HasSlug;
use App\Support\Markdown;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasPublicUrls, HasSlug;

    protected $guarded = ['id'];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_online' => 'boolean',
        'is_published' => 'boolean',
        'status' => EventStatus::class,
    ];

    /** Only published events marked public may appear on the website (BR-019). */
    public function scopeVisible(Builder $query): void
    {
        $query->where('is_published', true)->where('visibility', 'public');
    }

    public function scopeUpcoming(Builder $query): void
    {
        $query->where('starts_at', '>=', now()->startOfDay())->orderBy('starts_at');
    }

    public function scopePast(Builder $query): void
    {
        $query->where('starts_at', '<', now()->startOfDay())->orderByDesc('starts_at');
    }

    public function videos(): HasMany
    {
        return $this->hasMany(Video::class);
    }

    public function albums(): HasMany
    {
        return $this->hasMany(Album::class);
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    public function toCardArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'summary' => $this->summary,
            'starts_at' => $this->starts_at?->toIso8601String(),
            'ends_at' => $this->ends_at?->toIso8601String(),
            'timezone' => $this->timezone,
            'venue' => $this->venue,
            'location' => $this->location,
            'is_online' => $this->is_online,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'image_url' => self::publicUrl($this->image_path),
        ];
    }

    public function toDetailArray(): array
    {
        return $this->toCardArray() + [
            'description_html' => Markdown::render($this->description),
            'online_url' => $this->online_url,
            'registration_url' => $this->registration_url,
        ];
    }
}

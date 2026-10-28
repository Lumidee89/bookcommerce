<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Album extends Model
{
    use HasSlug;

    protected $guarded = ['id'];

    protected $casts = [
        'event_date' => 'date',
        'is_published' => 'boolean',
    ];

    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true)->orderBy('sort_order')->orderByDesc('event_date');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class)->orderBy('sort_order')->orderBy('id');
    }

    public function coverPhoto(): BelongsTo
    {
        return $this->belongsTo(Photo::class, 'cover_photo_id');
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function coverUrl(): ?string
    {
        $cover = $this->coverPhoto ?? $this->photos->first();

        return $cover?->thumbUrl();
    }

    public function toCardArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'event_date' => $this->event_date?->toDateString(),
            'location' => $this->location,
            'cover_url' => $this->coverUrl(),
            'photos_count' => $this->photos_count ?? $this->photos->count(),
        ];
    }
}

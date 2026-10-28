<?php

namespace App\Models;

use App\Enums\ArticleStatus;
use App\Models\Concerns\HasPublicUrls;
use App\Models\Concerns\HasSlug;
use App\Support\Markdown;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Article extends Model
{
    use HasPublicUrls, HasSlug;

    protected $guarded = ['id'];

    protected $casts = [
        'status' => ArticleStatus::class,
        'published_at' => 'datetime',
        'related_links' => 'array',
    ];

    /** Published, or scheduled and now past its publication time. */
    public function scopeLive(Builder $query): void
    {
        $query->whereIn('status', [ArticleStatus::Published, ArticleStatus::Scheduled])
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function isLive(): bool
    {
        return in_array($this->status, [ArticleStatus::Published, ArticleStatus::Scheduled], true)
            && $this->published_at !== null && $this->published_at->lte(now());
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ArticleCategory::class, 'article_category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function toCardArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'summary' => $this->summary,
            'image_url' => self::publicUrl($this->featured_image_path),
            'published_at' => $this->published_at?->toIso8601String(),
            'category' => $this->category ? ['name' => $this->category->name, 'slug' => $this->category->slug] : null,
            'author' => $this->author_name ?: 'Yemi Adebanjo',
        ];
    }

    public function toDetailArray(): array
    {
        return $this->toCardArray() + [
            'body_html' => Markdown::render($this->body),
            'tags' => $this->tags->map(fn (Tag $tag) => ['name' => $tag->name, 'slug' => $tag->slug])->all(),
            'related_links' => $this->related_links ?? [],
        ];
    }
}

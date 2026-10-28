<?php

namespace App\Models;

use App\Models\Concerns\HasPublicUrls;
use App\Models\Concerns\HasSlug;
use App\Support\Markdown;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ProfileSection extends Model
{
    use HasPublicUrls, HasSlug;

    protected $guarded = ['id'];

    protected $casts = ['is_published' => 'boolean'];

    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true)->orderBy('sort_order');
    }

    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'body_html' => Markdown::render($this->body),
            'image_url' => self::publicUrl($this->image_path),
            'image_alt' => $this->image_alt,
        ];
    }
}

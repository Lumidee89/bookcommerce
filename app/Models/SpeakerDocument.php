<?php

namespace App\Models;

use App\Models\Concerns\HasPublicUrls;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SpeakerDocument extends Model
{
    use HasPublicUrls;

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
            'description' => $this->description,
            'url' => self::publicUrl($this->file_path),
        ];
    }
}

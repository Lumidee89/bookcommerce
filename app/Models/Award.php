<?php

namespace App\Models;

use App\Enums\AwardCategory;
use App\Models\Concerns\HasPublicUrls;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Awards, qualifications, certifications and memberships share one table, separated by category.
 */
class Award extends Model
{
    use HasPublicUrls;

    protected $guarded = ['id'];

    protected $casts = [
        'category' => AwardCategory::class,
        'awarded_on' => 'date',
        'is_approved' => 'boolean',
        'is_featured' => 'boolean',
    ];

    /** Only owner-approved entries are public (BR-012). */
    public function scopeApproved(Builder $query): void
    {
        $query->where('is_approved', true)->orderBy('sort_order')->orderByDesc('awarded_on');
    }

    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'category' => $this->category->value,
            'category_label' => $this->category->label(),
            'title' => $this->title,
            'issuer' => $this->issuer,
            'awarded_on' => $this->awarded_on?->toDateString(),
            'description' => $this->description,
            'image_url' => self::publicUrl($this->image_path),
            'certificate_url' => self::publicUrl($this->certificate_path),
            'url' => $this->url,
        ];
    }
}

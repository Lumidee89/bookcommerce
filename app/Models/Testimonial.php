<?php

namespace App\Models;

use App\Enums\TestimonialStatus;
use App\Models\Concerns\HasPublicUrls;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    use HasPublicUrls;

    protected $guarded = ['id'];

    protected $casts = [
        'status' => TestimonialStatus::class,
        'permission_granted' => 'boolean',
        'permission_recorded_at' => 'datetime',
        'is_featured' => 'boolean',
    ];

    /** Public testimonials need both approval and recorded permission (BR-011). */
    public function scopePublic(Builder $query): void
    {
        $query->where('status', TestimonialStatus::Approved)->where('permission_granted', true)->orderBy('sort_order')->latest();
    }

    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'role' => $this->role,
            'organisation' => $this->organisation,
            'body' => $this->body,
            'photo_url' => self::publicUrl($this->photo_path),
            'video_url' => $this->video_url,
        ];
    }
}

<?php

namespace App\Models;

use App\Enums\DeliveryMethod;
use App\Models\Concerns\HasSlug;
use App\Support\Markdown;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConsultationService extends Model
{
    use HasSlug;

    protected $guarded = ['id'];

    protected $casts = [
        'delivery_method' => DeliveryMethod::class,
        'is_published' => 'boolean',
        'fee' => 'integer',
        'duration_minutes' => 'integer',
    ];

    protected function slugSource(): string
    {
        return 'name';
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true)->orderBy('sort_order');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function requiresPayment(): bool
    {
        return $this->fee > 0;
    }

    public function toCardArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'summary' => $this->summary,
            'audience' => $this->audience,
            'duration_minutes' => $this->duration_minutes,
            'fee' => $this->fee,
            'currency' => $this->currency,
            'delivery_method' => $this->delivery_method->value,
            'delivery_method_label' => $this->delivery_method->label(),
        ];
    }

    public function toDetailArray(): array
    {
        return $this->toCardArray() + [
            'description_html' => Markdown::render($this->description),
            'preparation_instructions' => $this->preparation_instructions,
            'cancellation_policy' => $this->cancellation_policy,
            'rescheduling_policy' => $this->rescheduling_policy,
        ];
    }
}

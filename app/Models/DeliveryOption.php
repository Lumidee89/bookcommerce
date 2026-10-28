<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DeliveryOption extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['is_active' => 'boolean', 'fee' => 'integer'];

    public function scopeActiveFor(Builder $query, string $currency): void
    {
        $query->where('is_active', true)->where('currency', $currency)->orderBy('sort_order');
    }

    public function estimate(): ?string
    {
        if (! $this->estimated_days_min && ! $this->estimated_days_max) {
            return null;
        }

        if ($this->estimated_days_min && $this->estimated_days_max && $this->estimated_days_min !== $this->estimated_days_max) {
            return "{$this->estimated_days_min}–{$this->estimated_days_max} working days";
        }

        $days = $this->estimated_days_max ?: $this->estimated_days_min;

        return "{$days} working ".str('day')->plural($days);
    }

    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'fee' => $this->fee,
            'currency' => $this->currency,
            'estimate' => $this->estimate(),
        ];
    }
}

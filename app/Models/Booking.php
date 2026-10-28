<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Models\Concerns\HasInternalNotes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\URL;

class Booking extends Model
{
    use HasInternalNotes;

    protected $guarded = ['id'];

    protected $hidden = ['manage_token', 'slot_lock'];

    protected $casts = [
        'status' => BookingStatus::class,
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'hold_expires_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'reminder_sent_at' => 'datetime',
        'fee' => 'integer',
    ];

    public function getRouteKeyName(): string
    {
        return 'reference';
    }

    /** Bookings that currently occupy the calendar: confirmed ones, plus unexpired payment holds. */
    public function scopeOccupying(Builder $query): void
    {
        $query->where(function ($q) {
            $q->whereIn('status', [BookingStatus::Confirmed, BookingStatus::RequiresAttention])
                ->orWhere(fn ($q) => $q->where('status', BookingStatus::PendingPayment)->where('hold_expires_at', '>', now()));
        });
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(ConsultationService::class, 'consultation_service_id');
    }

    public function transactions(): MorphMany
    {
        return $this->morphMany(Transaction::class, 'payable')->latest();
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(BookingStatusHistory::class)->latest('id');
    }

    public function transitionTo(BookingStatus $status, ?string $note = null, ?int $userId = null): void
    {
        $from = $this->status;
        $this->status = $status;

        if (in_array($status, [BookingStatus::Cancelled, BookingStatus::Expired], true)) {
            $this->slot_lock = null;
            if ($status === BookingStatus::Cancelled) {
                $this->cancelled_at ??= now();
            }
        }

        if ($status === BookingStatus::Confirmed) {
            $this->confirmed_at ??= now();
            $this->hold_expires_at = null;
        }

        $this->save();

        $this->statusHistory()->create([
            'from_status' => $from?->value,
            'to_status' => $status->value,
            'note' => $note,
            'user_id' => $userId,
            'created_at' => now(),
        ]);
    }

    public function manageUrl(): string
    {
        return URL::signedRoute('bookings.manage', ['booking' => $this->reference, 'token' => $this->manage_token]);
    }

    public function toPublicArray(): array
    {
        return [
            'reference' => $this->reference,
            'service_name' => $this->service_name,
            'service_slug' => $this->service?->slug,
            'starts_at' => $this->starts_at->toIso8601String(),
            'ends_at' => $this->ends_at->toIso8601String(),
            'client_timezone' => $this->client_timezone,
            'name' => $this->name,
            'email' => $this->email,
            'fee' => $this->fee,
            'currency' => $this->currency,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'delivery_method' => $this->delivery_method,
            'meeting_url' => $this->status === BookingStatus::Confirmed ? $this->meeting_url : null,
            'meeting_details' => $this->status === BookingStatus::Confirmed ? $this->meeting_details : null,
            'hold_expires_at' => $this->hold_expires_at?->toIso8601String(),
        ];
    }
}

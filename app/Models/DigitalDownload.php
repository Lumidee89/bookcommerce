<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DigitalDownload extends Model
{
    protected $guarded = ['id'];

    protected $hidden = ['token'];

    protected $casts = [
        'expires_at' => 'datetime',
        'last_downloaded_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }

    public function format(): BelongsTo
    {
        return $this->belongsTo(BookFormat::class, 'book_format_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(DownloadEvent::class);
    }

    public function unavailableReason(): ?string
    {
        return match (true) {
            $this->revoked_at !== null => 'revoked',
            $this->expires_at->isPast() => 'expired',
            $this->download_count >= $this->max_downloads => 'limit_reached',
            ! $this->order?->isPaid() => 'unpaid',
            default => null,
        };
    }

    public function url(): string
    {
        return route('downloads.show', $this->token);
    }
}

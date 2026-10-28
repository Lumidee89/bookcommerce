<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Models\Concerns\HasInternalNotes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\URL;

class Order extends Model
{
    use HasInternalNotes;

    protected $guarded = ['id'];

    protected $hidden = ['access_token'];

    protected $casts = [
        'status' => OrderStatus::class,
        'requires_shipping' => 'boolean',
        'stock_applied' => 'boolean',
        'paid_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public const PAID_STATUSES = ['paid', 'processing', 'ready_for_dispatch', 'shipped', 'delivered', 'completed', 'partially_refunded'];

    public function getRouteKeyName(): string
    {
        return 'reference';
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function transactions(): MorphMany
    {
        return $this->morphMany(Transaction::class, 'payable')->latest();
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->latest('id');
    }

    public function downloads(): HasMany
    {
        return $this->hasMany(DigitalDownload::class);
    }

    public function isPaid(): bool
    {
        return in_array($this->status->value, self::PAID_STATUSES, true);
    }

    public function transitionTo(OrderStatus $status, ?string $note = null, ?int $userId = null): void
    {
        $from = $this->status;
        $this->status = $status;

        match ($status) {
            OrderStatus::Shipped => $this->shipped_at ??= now(),
            OrderStatus::Delivered => $this->delivered_at ??= now(),
            OrderStatus::Cancelled => $this->cancelled_at ??= now(),
            default => null,
        };

        $this->save();

        $this->statusHistory()->create([
            'from_status' => $from?->value,
            'to_status' => $status->value,
            'note' => $note,
            'user_id' => $userId,
            'created_at' => now(),
        ]);
    }

    /** A signed, customer-specific link to the order summary page. */
    public function customerUrl(): string
    {
        return URL::signedRoute('orders.show', ['order' => $this->reference, 'token' => $this->access_token]);
    }
}

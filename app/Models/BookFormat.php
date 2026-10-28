<?php

namespace App\Models;

use App\Enums\BookFormatType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookFormat extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'type' => BookFormatType::class,
        'is_digital' => 'boolean',
        'is_active' => 'boolean',
        'stock_quantity' => 'integer',
    ];

    protected $hidden = ['file_path'];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(BookPrice::class);
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class)->latest();
    }

    public function priceFor(string $currency): ?int
    {
        return $this->prices->firstWhere('currency', $currency)?->amount;
    }

    public function inStock(int $quantity = 1): bool
    {
        return $this->is_digital || ($this->stock_quantity ?? 0) >= $quantity;
    }

    public function displayLabel(): string
    {
        return $this->label ?: $this->type->label();
    }
}

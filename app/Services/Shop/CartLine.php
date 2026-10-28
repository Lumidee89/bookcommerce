<?php

namespace App\Services\Shop;

use App\Models\BookFormat;

final class CartLine
{
    public function __construct(
        public readonly BookFormat $format,
        public readonly int $quantity,
        public readonly ?int $unitPrice,
    ) {}

    public function total(): int
    {
        return ($this->unitPrice ?? 0) * $this->quantity;
    }

    public function toArray(): array
    {
        $book = $this->format->book;

        return [
            'format_id' => $this->format->id,
            'book_title' => $book->title,
            'book_slug' => $book->slug,
            'cover_url' => $book->coverUrl(),
            'format_label' => $this->format->displayLabel(),
            'is_digital' => $this->format->is_digital,
            'quantity' => $this->quantity,
            'unit_price' => $this->unitPrice,
            'line_total' => $this->total(),
            'stock_quantity' => $this->format->is_digital ? null : $this->format->stock_quantity,
        ];
    }
}

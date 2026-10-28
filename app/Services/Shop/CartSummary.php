<?php

namespace App\Services\Shop;

final class CartSummary
{
    /**
     * @param  list<CartLine>  $lines
     * @param  list<string>  $issues
     */
    public function __construct(
        public readonly string $currency,
        public readonly array $lines,
        public readonly array $issues,
    ) {}

    public function subtotal(): int
    {
        return array_sum(array_map(fn (CartLine $l) => $l->total(), $this->lines));
    }

    public function requiresShipping(): bool
    {
        foreach ($this->lines as $line) {
            if (! $line->format->is_digital) {
                return true;
            }
        }

        return false;
    }

    public function isEmpty(): bool
    {
        return $this->lines === [];
    }

    public function canCheckout(): bool
    {
        return ! $this->isEmpty() && $this->issues === [];
    }

    public function toArray(): array
    {
        return [
            'currency' => $this->currency,
            'lines' => array_map(fn (CartLine $l) => $l->toArray(), $this->lines),
            'subtotal' => $this->subtotal(),
            'requires_shipping' => $this->requiresShipping(),
            'issues' => array_values(array_unique($this->issues)),
            'can_checkout' => $this->canCheckout(),
        ];
    }
}

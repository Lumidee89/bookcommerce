<?php

namespace App\Services\Shop;

use App\Models\BookFormat;
use Illuminate\Contracts\Session\Session;

/**
 * Session-backed cart. It only stores format IDs and quantities; every price and
 * availability check is recalculated from the database (PRD 5.4).
 */
class CartService
{
    private const KEY = 'cart.items';

    private const CURRENCY_KEY = 'cart.currency';

    public function __construct(private readonly Session $session) {}

    public function currency(): string
    {
        $currency = $this->session->get(self::CURRENCY_KEY, config('platform.default_currency'));

        return in_array($currency, config('platform.currencies'), true) ? $currency : config('platform.default_currency');
    }

    public function setCurrency(string $currency): void
    {
        if (in_array($currency, config('platform.currencies'), true)) {
            $this->session->put(self::CURRENCY_KEY, $currency);
        }
    }

    /** @return array<int, int> format id => quantity */
    public function items(): array
    {
        return $this->session->get(self::KEY, []);
    }

    public function count(): int
    {
        return array_sum($this->items());
    }

    public function add(BookFormat $format, int $quantity = 1): void
    {
        $items = $this->items();
        $items[$format->id] = ($items[$format->id] ?? 0) + $quantity;
        $this->store($items);
    }

    public function update(int $formatId, int $quantity): void
    {
        $items = $this->items();
        if ($quantity <= 0) {
            unset($items[$formatId]);
        } else {
            $items[$formatId] = $quantity;
        }
        $this->store($items);
    }

    public function clear(): void
    {
        $this->session->forget(self::KEY);
    }

    public function summary(): CartSummary
    {
        $currency = $this->currency();
        $items = $this->items();

        $formats = BookFormat::with(['book', 'prices'])->whereIn('id', array_keys($items))->get()->keyBy('id');

        $lines = [];
        $issues = [];
        foreach ($items as $formatId => $quantity) {
            $format = $formats->get($formatId);
            if (! $format || ! $format->is_active || ! $format->book->isPurchasable()) {
                $issues[] = 'An item in your cart is no longer available and was removed.';
                $this->update($formatId, 0);

                continue;
            }

            // Digital copies are licensed per customer; one is enough.
            $quantity = $format->is_digital ? 1 : max(1, $quantity);
            $price = $format->book->formatPrice($format, $currency);

            if ($price === null) {
                $issues[] = "“{$format->book->title}” ({$format->displayLabel()}) is not sold in {$currency}.";
            } elseif (! $format->inStock($quantity)) {
                $issues[] = "Only {$format->stock_quantity} cop".($format->stock_quantity === 1 ? 'y' : 'ies')." of “{$format->book->title}” ({$format->displayLabel()}) available.";
            }

            $lines[] = new CartLine($format, $quantity, $price);
        }

        return new CartSummary($currency, $lines, $issues);
    }

    private function store(array $items): void
    {
        $this->session->put(self::KEY, array_filter($items, fn ($q) => $q > 0));
    }
}

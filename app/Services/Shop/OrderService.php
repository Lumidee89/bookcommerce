<?php

namespace App\Services\Shop;

use App\Enums\OrderStatus;
use App\Models\BookFormat;
use App\Models\DeliveryOption;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    /**
     * Create a pending order with authoritative, server-calculated totals and price snapshots.
     *
     * @param  array<string, mixed>  $customer  validated checkout fields
     */
    public function createFromCart(CartSummary $cart, array $customer, ?DeliveryOption $delivery, ?string $ip = null): Order
    {
        if (! $cart->canCheckout()) {
            throw new CheckoutException($cart->issues[0] ?? 'Your cart is empty.');
        }

        if ($cart->requiresShipping() && (! $delivery || $delivery->currency !== $cart->currency || ! $delivery->is_active)) {
            throw new CheckoutException('Please choose a delivery option.');
        }

        return DB::transaction(function () use ($cart, $customer, $delivery, $ip) {
            // Re-check stock under lock so concurrent checkouts see consistent quantities.
            $formats = BookFormat::whereIn('id', array_map(fn (CartLine $l) => $l->format->id, $cart->lines))->lockForUpdate()->get()->keyBy('id');
            foreach ($cart->lines as $line) {
                if (! $formats[$line->format->id]->inStock($line->quantity)) {
                    throw new CheckoutException("“{$line->format->book->title}” no longer has enough stock.");
                }
            }

            $shipping = $cart->requiresShipping();
            $deliveryFee = $shipping ? $delivery->fee : 0;

            $order = Order::create([
                'reference' => $this->newReference(),
                'access_token' => Str::random(40),
                'customer_name' => $customer['name'],
                'email' => strtolower($customer['email']),
                'phone' => $customer['phone'] ?? null,
                'currency' => $cart->currency,
                'subtotal' => $cart->subtotal(),
                'delivery_fee' => $deliveryFee,
                'total' => $cart->subtotal() + $deliveryFee,
                'status' => OrderStatus::PendingPayment,
                'requires_shipping' => $shipping,
                'delivery_option_id' => $shipping ? $delivery->id : null,
                'delivery_option_name' => $shipping ? $delivery->name : null,
                'delivery_estimate' => $shipping ? $delivery->estimate() : null,
                'shipping_address_line1' => $shipping ? $customer['address_line1'] : null,
                'shipping_address_line2' => $shipping ? ($customer['address_line2'] ?? null) : null,
                'shipping_city' => $shipping ? $customer['city'] : null,
                'shipping_state' => $shipping ? ($customer['state'] ?? null) : null,
                'shipping_postal_code' => $shipping ? ($customer['postal_code'] ?? null) : null,
                'shipping_country' => $shipping ? $customer['country'] : null,
                'customer_notes' => $customer['notes'] ?? null,
                'ip_address' => $ip,
            ]);

            foreach ($cart->lines as $line) {
                $order->items()->create([
                    'book_id' => $line->format->book_id,
                    'book_format_id' => $line->format->id,
                    'title' => $line->format->book->title,
                    'format_label' => $line->format->displayLabel(),
                    'is_digital' => $line->format->is_digital,
                    'unit_price' => $line->unitPrice,
                    'quantity' => $line->quantity,
                    'line_total' => $line->total(),
                ]);
            }

            $order->statusHistory()->create(['to_status' => OrderStatus::PendingPayment->value, 'note' => 'Order placed', 'created_at' => now()]);

            return $order;
        });
    }

    private function newReference(): string
    {
        do {
            $reference = 'YA-'.now()->format('ymd').'-'.strtoupper(Str::random(5));
        } while (Order::where('reference', $reference)->exists());

        return $reference;
    }
}

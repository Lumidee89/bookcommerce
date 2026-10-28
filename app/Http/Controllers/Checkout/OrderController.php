<?php

namespace App\Http\Controllers\Checkout;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Support\Seo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    /** Reached only through a signed link that carries the order's private token. */
    public function show(Request $request, Order $order): Response
    {
        abort_unless(hash_equals($order->access_token, (string) $request->query('token')), 403);
        $order->load(['items.download']);

        return Inertia::render('checkout/order', [
            'order' => [
                'reference' => $order->reference,
                'status' => $order->status->value,
                'status_label' => $order->status->label(),
                'is_paid' => $order->isPaid(),
                'customer_name' => $order->customer_name,
                'email' => $order->email,
                'currency' => $order->currency,
                'subtotal' => $order->subtotal,
                'delivery_fee' => $order->delivery_fee,
                'total' => $order->total,
                'requires_shipping' => $order->requires_shipping,
                'delivery_option_name' => $order->delivery_option_name,
                'delivery_estimate' => $order->delivery_estimate,
                'shipping_reference' => $order->shipping_reference,
                'shipping_carrier' => $order->shipping_carrier,
                'created_at' => $order->created_at->toIso8601String(),
                'items' => $order->items->map(fn ($item) => [
                    'title' => $item->title,
                    'format_label' => $item->format_label,
                    'quantity' => $item->quantity,
                    'line_total' => $item->line_total,
                    'is_digital' => $item->is_digital,
                    'download' => $order->isPaid() && $item->download ? [
                        'url' => $item->download->url(),
                        'available' => $item->download->unavailableReason() === null,
                        'remaining' => max(0, $item->download->max_downloads - $item->download->download_count),
                        'expires_at' => $item->download->expires_at->toIso8601String(),
                    ] : null,
                ]),
            ],
            'retryUrl' => URL::route('checkout.retry', ['order' => $order->reference, 'token' => $order->access_token]),
            'seo' => Seo::make("Order {$order->reference}"),
        ]);
    }
}

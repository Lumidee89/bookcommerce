<?php

namespace App\Http\Controllers\Checkout;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Models\DeliveryOption;
use App\Models\Order;
use App\Services\Payments\PaymentException;
use App\Services\Payments\PaymentService;
use App\Services\Shop\CartService;
use App\Services\Shop\CheckoutException;
use App\Services\Shop\OrderFulfilmentService;
use App\Services\Shop\OrderService;
use App\Support\Seo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $cart,
        private readonly OrderService $orders,
        private readonly PaymentService $payments,
    ) {}

    public function show(): Response|HttpResponse
    {
        $summary = $this->cart->summary();
        if ($summary->isEmpty()) {
            return redirect()->route('cart.show');
        }

        return Inertia::render('checkout/checkout', [
            'summary' => $summary->toArray(),
            'deliveryOptions' => DeliveryOption::activeFor($summary->currency)->get()->map->toPublicArray(),
            'paymentsEnabled' => $this->payments->available(),
            'seo' => Seo::make('Checkout'),
        ]);
    }

    public function store(CheckoutRequest $request, OrderFulfilmentService $fulfilment): HttpResponse
    {
        $summary = $this->cart->summary();
        $delivery = $request->integer('delivery_option_id') ? DeliveryOption::find($request->integer('delivery_option_id')) : null;

        try {
            $order = $this->orders->createFromCart($summary, $request->validated(), $delivery, $request->ip());
        } catch (CheckoutException $e) {
            return back()->with('error', $e->getMessage());
        }

        // Nothing to charge (e.g. free e-books): no gateway involved.
        if ($order->total === 0) {
            $fulfilment->markPaid($order);
            $this->cart->clear();

            return Inertia::location($order->customerUrl());
        }

        if (! $this->payments->available()) {
            return redirect($order->customerUrl())->with('error', 'Online payment is not available yet. Your order has been saved; we will contact you about payment.');
        }

        try {
            $transaction = $this->payments->createTransaction($order, $order->total, $order->currency);
            $url = $this->payments->checkoutUrl($transaction, $order->email);
        } catch (PaymentException $e) {
            Log::error('Checkout payment initialisation failed', ['order' => $order->reference, 'error' => $e->getMessage()]);

            return redirect($order->customerUrl())->with('error', 'We could not start the payment. Please try again from your order page.');
        }

        $this->cart->clear();

        return Inertia::location($url);
    }

    /** Retry payment for an unpaid order from the customer's signed order page. */
    public function retry(Request $request, Order $order): HttpResponse
    {
        abort_unless(hash_equals($order->access_token, (string) $request->query('token')), 403);
        if ($order->isPaid() || ! $this->payments->available() || $order->status->value === 'cancelled') {
            return redirect($order->customerUrl());
        }

        $transaction = $this->payments->createTransaction($order, $order->total, $order->currency);

        try {
            return Inertia::location($this->payments->checkoutUrl($transaction, $order->email));
        } catch (PaymentException) {
            return redirect($order->customerUrl())->with('error', 'We could not start the payment. Please try again shortly.');
        }
    }
}

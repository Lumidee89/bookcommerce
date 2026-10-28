<?php

namespace App\Http\Controllers\Checkout;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Order;
use App\Models\Transaction;
use App\Services\Payments\PaymentService;
use App\Services\Payments\TestGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $payments) {}

    /**
     * The customer's browser returns here. The redirect itself proves nothing:
     * we always verify with the provider before changing any state (PRD 5.4).
     */
    public function callback(Request $request): RedirectResponse
    {
        $reference = (string) ($request->query('reference') ?? $request->query('trxref'));
        $transaction = $this->payments->confirm($reference);
        abort_unless($transaction, 404);

        $payable = $transaction->payable;
        $message = match ($transaction->status->value) {
            'success' => ['success', 'Payment received. Thank you!'],
            'pending' => ['error', 'Your payment is still being confirmed. This page will update once it is verified.'],
            default => ['error', 'The payment was not completed. You can try again.'],
        };

        return match (true) {
            $payable instanceof Order => redirect($payable->customerUrl())->with(...$message),
            $payable instanceof Booking => redirect($payable->refresh()->manageUrl())->with(...$message),
            default => redirect()->route('home'),
        };
    }

    public function webhook(Request $request): JsonResponse
    {
        $gateway = $this->payments->gateway();
        if (! $gateway->hasValidWebhookSignature($request)) {
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        if ($reference = $gateway->referenceFromWebhook($request)) {
            $this->payments->confirm($reference);
        }

        return response()->json(['received' => true]);
    }

    /** Local sandbox "provider" page. Only reachable when the test gateway is active outside production. */
    public function sandbox(Transaction $transaction): Response
    {
        abort_unless($this->payments->gateway() instanceof TestGateway && $this->payments->available(), 404);

        return Inertia::render('checkout/sandbox', [
            'transaction' => ['reference' => $transaction->reference, 'amount' => $transaction->amount, 'currency' => $transaction->currency, 'status' => $transaction->status->value],
        ]);
    }

    public function simulate(Request $request, Transaction $transaction): \Symfony\Component\HttpFoundation\Response
    {
        $gateway = $this->payments->gateway();
        abort_unless($gateway instanceof TestGateway && $this->payments->available(), 404);
        $data = $request->validate(['outcome' => ['required', 'in:success,failed']]);

        return Inertia::location($gateway->simulate($transaction, $data['outcome']));
    }
}

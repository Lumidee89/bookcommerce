<?php

namespace App\Services\Payments;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Local sandbox used to exercise the full checkout flow without a real provider.
 * It is clearly labelled in the UI and is refused outside local/testing environments.
 */
class TestGateway implements PaymentGateway
{
    public function name(): string
    {
        return 'test';
    }

    public function isConfigured(): bool
    {
        return app()->environment(['local', 'testing']);
    }

    public function initialize(Transaction $transaction, string $email, string $callbackUrl): string
    {
        Cache::put($this->key($transaction->reference).':callback', $callbackUrl, now()->addDay());

        return route('payments.sandbox', $transaction->reference);
    }

    public function verify(string $reference): PaymentVerification
    {
        $outcome = Cache::get($this->key($reference));
        if (! $outcome) {
            return new PaymentVerification('pending');
        }

        return new PaymentVerification(
            status: $outcome['status'],
            amount: $outcome['amount'],
            currency: $outcome['currency'],
            gatewayReference: 'SANDBOX-'.$reference,
            channel: 'sandbox',
            raw: $outcome,
        );
    }

    /** Called by the sandbox page to simulate the provider recording an outcome. */
    public function simulate(Transaction $transaction, string $status): string
    {
        Cache::put($this->key($transaction->reference), [
            'status' => $status,
            'amount' => $transaction->amount,
            'currency' => $transaction->currency,
        ], now()->addDay());

        return Cache::get($this->key($transaction->reference).':callback', route('home'));
    }

    public function hasValidWebhookSignature(Request $request): bool
    {
        return false;
    }

    public function referenceFromWebhook(Request $request): ?string
    {
        return null;
    }

    private function key(string $reference): string
    {
        return 'sandbox-payment:'.$reference;
    }
}

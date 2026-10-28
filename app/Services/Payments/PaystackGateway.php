<?php

namespace App\Services\Payments;

use App\Models\Transaction;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

/**
 * Paystack Standard checkout. Docs: https://paystack.com/docs/payments/accept-payments/
 */
class PaystackGateway implements PaymentGateway
{
    public function __construct(private readonly array $config) {}

    public function name(): string
    {
        return 'paystack';
    }

    public function isConfigured(): bool
    {
        return filled($this->config['secret_key'] ?? null);
    }

    public function initialize(Transaction $transaction, string $email, string $callbackUrl): string
    {
        $response = $this->http()->post('/transaction/initialize', [
            'email' => $email,
            'amount' => $transaction->amount,
            'currency' => $transaction->currency,
            'reference' => $transaction->reference,
            'callback_url' => $callbackUrl,
            'metadata' => ['payable' => class_basename($transaction->payable_type), 'payable_id' => $transaction->payable_id],
        ]);

        if (! $response->successful() || ! $response->json('status')) {
            throw new PaymentException($response->json('message') ?: 'Unable to start payment with Paystack.');
        }

        return $response->json('data.authorization_url');
    }

    public function verify(string $reference): PaymentVerification
    {
        $response = $this->http()->get('/transaction/verify/'.rawurlencode($reference));

        if (! $response->successful() || ! $response->json('status')) {
            return new PaymentVerification('pending', message: $response->json('message') ?: 'Verification failed.');
        }

        $data = $response->json('data');

        return new PaymentVerification(
            status: (string) Arr::get($data, 'status'),
            amount: (int) Arr::get($data, 'amount'),
            currency: Arr::get($data, 'currency'),
            gatewayReference: (string) Arr::get($data, 'id'),
            channel: Arr::get($data, 'channel'),
            message: Arr::get($data, 'gateway_response'),
            // Keep a minimal, non-sensitive record only.
            raw: Arr::only($data, ['id', 'status', 'reference', 'amount', 'currency', 'channel', 'gateway_response', 'paid_at']),
        );
    }

    public function hasValidWebhookSignature(Request $request): bool
    {
        $signature = (string) $request->header('x-paystack-signature');

        return $this->isConfigured() && $signature !== ''
            && hash_equals(hash_hmac('sha512', $request->getContent(), $this->config['secret_key']), $signature);
    }

    public function referenceFromWebhook(Request $request): ?string
    {
        return str_starts_with((string) $request->input('event'), 'charge.') ? $request->input('data.reference') : null;
    }

    private function http(): PendingRequest
    {
        return Http::baseUrl($this->config['base_url'])
            ->withToken($this->config['secret_key'])
            ->acceptJson()
            ->timeout(20)
            ->retry(2, 500, throw: false);
    }
}

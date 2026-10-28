<?php

namespace App\Services\Payments;

use App\Models\Transaction;
use Illuminate\Http\Request;

class NullGateway implements PaymentGateway
{
    public function name(): string
    {
        return 'none';
    }

    public function isConfigured(): bool
    {
        return false;
    }

    public function initialize(Transaction $transaction, string $email, string $callbackUrl): string
    {
        throw new PaymentException('Online payments have not been configured yet.');
    }

    public function verify(string $reference): PaymentVerification
    {
        return new PaymentVerification('pending', message: 'No payment gateway configured.');
    }

    public function hasValidWebhookSignature(Request $request): bool
    {
        return false;
    }

    public function referenceFromWebhook(Request $request): ?string
    {
        return null;
    }
}

<?php

namespace App\Services\Payments;

use App\Models\Transaction;
use Illuminate\Http\Request;

interface PaymentGateway
{
    public function name(): string;

    /** Whether credentials are present; an unconfigured gateway must never be offered as working (BR-020). */
    public function isConfigured(): bool;

    /** Start a hosted checkout and return the URL the customer should be sent to. */
    public function initialize(Transaction $transaction, string $email, string $callbackUrl): string;

    /** Ask the provider, server-to-server, what actually happened to a payment. */
    public function verify(string $reference): PaymentVerification;

    public function hasValidWebhookSignature(Request $request): bool;

    /** Extract the transaction reference from a webhook payload, if it concerns a charge. */
    public function referenceFromWebhook(Request $request): ?string;
}

<?php

namespace App\Services\Payments;

use App\Enums\TransactionStatus;
use App\Models\Booking;
use App\Models\Order;
use App\Models\Transaction;
use App\Services\Bookings\BookingService;
use App\Services\Shop\OrderFulfilmentService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentService
{
    public function __construct(
        private readonly PaymentGateway $gateway,
        private readonly OrderFulfilmentService $orders,
        private readonly BookingService $bookings,
    ) {}

    public function gateway(): PaymentGateway
    {
        return $this->gateway;
    }

    public function available(): bool
    {
        return $this->gateway->isConfigured();
    }

    public function createTransaction(Model $payable, int $amount, string $currency): Transaction
    {
        return $payable->transactions()->create([
            'gateway' => $this->gateway->name(),
            'reference' => 'TX-'.strtoupper(Str::random(16)),
            'amount' => $amount,
            'currency' => $currency,
            'status' => TransactionStatus::Pending,
        ]);
    }

    public function checkoutUrl(Transaction $transaction, string $email): string
    {
        return $this->gateway->initialize($transaction, $email, route('payments.callback', ['reference' => $transaction->reference]));
    }

    /**
     * Verify a transaction with the provider and apply the outcome exactly once.
     * Safe to call repeatedly from callbacks and webhooks (BR-006).
     */
    public function confirm(string $reference): ?Transaction
    {
        $transaction = Transaction::where('reference', $reference)->first();
        if (! $transaction) {
            return null;
        }

        if ($transaction->status !== TransactionStatus::Pending) {
            return $transaction;
        }

        $verification = $this->gateway->verify($reference);

        return DB::transaction(function () use ($transaction, $verification) {
            $transaction = Transaction::whereKey($transaction->id)->lockForUpdate()->first();
            if ($transaction->status !== TransactionStatus::Pending) {
                return $transaction;
            }

            if ($verification->successful()) {
                if ($verification->amount !== $transaction->amount || strtoupper((string) $verification->currency) !== $transaction->currency) {
                    Log::warning('Payment amount/currency mismatch', ['reference' => $transaction->reference]);
                    $this->markFailed($transaction, $verification, 'Amount or currency did not match the order.');

                    return $transaction;
                }

                $transaction->update([
                    'status' => TransactionStatus::Success,
                    'gateway_reference' => $verification->gatewayReference,
                    'channel' => $verification->channel,
                    'gateway_response' => $verification->raw,
                    'verified_at' => now(),
                ]);

                match (true) {
                    $transaction->payable instanceof Order => $this->orders->markPaid($transaction->payable),
                    // Runs after commit: booking confirmation takes its own calendar lock.
                    $transaction->payable instanceof Booking => DB::afterCommit(fn () => $this->bookings->markPaid($transaction->payable)),
                    default => null,
                };
            } elseif ($verification->failed()) {
                $this->markFailed($transaction, $verification, $verification->message);
            }

            return $transaction;
        });
    }

    private function markFailed(Transaction $transaction, PaymentVerification $verification, ?string $reason): void
    {
        $transaction->update([
            'status' => $verification->status === 'abandoned' ? TransactionStatus::Abandoned : TransactionStatus::Failed,
            'failure_reason' => $reason,
            'gateway_response' => $verification->raw,
            'verified_at' => now(),
        ]);

        if ($transaction->payable instanceof Order) {
            $this->orders->markPaymentFailed($transaction->payable);
        }
    }
}

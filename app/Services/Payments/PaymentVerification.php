<?php

namespace App\Services\Payments;

final class PaymentVerification
{
    public function __construct(
        public readonly string $status,
        public readonly ?int $amount = null,
        public readonly ?string $currency = null,
        public readonly ?string $gatewayReference = null,
        public readonly ?string $channel = null,
        public readonly ?string $message = null,
        public readonly array $raw = [],
    ) {}

    public function successful(): bool
    {
        return $this->status === 'success';
    }

    public function failed(): bool
    {
        return in_array($this->status, ['failed', 'abandoned', 'reversed'], true);
    }
}

<?php

namespace App\Enums;

enum OrderStatus: string
{
    use HasOptions;

    case PendingPayment = 'pending_payment';
    case PaymentProcessing = 'payment_processing';
    case Paid = 'paid';
    case Processing = 'processing';
    case ReadyForDispatch = 'ready_for_dispatch';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case PaymentFailed = 'payment_failed';
    case RefundPending = 'refund_pending';
    case PartiallyRefunded = 'partially_refunded';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::PendingPayment => 'Pending payment',
            self::PaymentProcessing => 'Payment processing',
            self::Paid => 'Paid',
            self::Processing => 'Processing',
            self::ReadyForDispatch => 'Ready for dispatch',
            self::Shipped => 'Shipped',
            self::Delivered => 'Delivered',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
            self::PaymentFailed => 'Payment failed',
            self::RefundPending => 'Refund pending',
            self::PartiallyRefunded => 'Partially refunded',
            self::Refunded => 'Refunded',
        };
    }
}

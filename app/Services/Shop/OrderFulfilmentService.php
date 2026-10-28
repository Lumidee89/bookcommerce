<?php

namespace App\Services\Shop;

use App\Enums\OrderStatus;
use App\Models\BookFormat;
use App\Models\DigitalDownload;
use App\Models\Order;
use App\Notifications\AdminAlert;
use App\Notifications\OrderPaid;
use App\Support\AdminRecipient;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class OrderFulfilmentService
{
    /**
     * Apply the consequences of a verified payment. Idempotent: a paid order is left untouched.
     */
    public function markPaid(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $order = Order::whereKey($order->id)->lockForUpdate()->first();
            if ($order->isPaid()) {
                return;
            }

            $order->paid_at = now();
            $order->transitionTo(OrderStatus::Paid, 'Payment verified');

            $this->applyStock($order);
            $this->issueDownloads($order);

            if (! $order->requires_shipping) {
                $order->transitionTo(OrderStatus::Completed, 'Digital order delivered by email');
            }

            DB::afterCommit(function () use ($order) {
                Notification::route('mail', [$order->email => $order->customer_name])->notify(new OrderPaid($order));
                AdminRecipient::notify(new AdminAlert(
                    "New paid order {$order->reference}",
                    ["{$order->customer_name} paid ".Money::format($order->total, $order->currency).'.',
                        $order->requires_shipping ? 'This order contains printed books awaiting fulfilment.' : 'Digital items were delivered automatically.'],
                    route('admin.orders.show', $order),
                ));
            });
        });
    }

    public function markPaymentFailed(Order $order): void
    {
        if ($order->status === OrderStatus::PendingPayment) {
            $order->transitionTo(OrderStatus::PaymentFailed, 'Payment was not completed');
        }
    }

    private function applyStock(Order $order): void
    {
        if ($order->stock_applied) {
            return;
        }

        foreach ($order->items()->where('is_digital', false)->get() as $item) {
            $format = BookFormat::whereKey($item->book_format_id)->lockForUpdate()->first();
            if (! $format) {
                continue;
            }

            $balance = ($format->stock_quantity ?? 0) - $item->quantity;
            if ($balance < 0) {
                $order->notes()->create(['body' => "Stock shortfall: “{$item->title}” ({$item->format_label}) was oversold by ".abs($balance).'.']);
            }

            $format->update(['stock_quantity' => max(0, $balance)]);
            $format->inventoryMovements()->create([
                'change' => -$item->quantity,
                'balance_after' => max(0, $balance),
                'reason' => 'sale',
                'order_id' => $order->id,
            ]);
        }

        $order->update(['stock_applied' => true]);
    }

    private function issueDownloads(Order $order): void
    {
        foreach ($order->items()->where('is_digital', true)->doesntHave('download')->get() as $item) {
            DigitalDownload::create([
                'order_id' => $order->id,
                'order_item_id' => $item->id,
                'book_format_id' => $item->book_format_id,
                'token' => Str::random(48),
                'email' => $order->email,
                'max_downloads' => config('platform.downloads.max_attempts'),
                'expires_at' => now()->addHours(config('platform.downloads.ttl_hours')),
            ]);
        }
    }

    /** Give a legitimate customer a fresh window and allowance (admin action). */
    public function renewDownload(DigitalDownload $download): void
    {
        $download->update([
            'expires_at' => now()->addHours(config('platform.downloads.ttl_hours')),
            'download_count' => 0,
            'revoked_at' => null,
        ]);
    }
}

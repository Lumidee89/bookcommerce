<?php

namespace App\Notifications;

use App\Models\Order;
use App\Support\Money;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderPaid extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $order = $this->order->load('items.download');

        $mail = (new MailMessage)
            ->subject("Your order {$order->reference} — receipt")
            ->greeting("Thank you, {$order->customer_name}.")
            ->line('Your payment has been received and verified. Here is your receipt.');

        foreach ($order->items as $item) {
            $mail->line("• {$item->title} ({$item->format_label}) × {$item->quantity} — ".Money::format($item->line_total, $order->currency));
        }

        if ($order->delivery_fee > 0) {
            $mail->line("Delivery ({$order->delivery_option_name}): ".Money::format($order->delivery_fee, $order->currency));
        }

        $mail->line('**Total paid: '.Money::format($order->total, $order->currency).'**');

        $downloads = $order->items->pluck('download')->filter();
        if ($downloads->isNotEmpty()) {
            $mail->line('**Your e-books**');
            foreach ($downloads as $download) {
                $item = $order->items->firstWhere('id', $download->order_item_id);
                $mail->line("[Download {$item->title} ({$item->format_label})]({$download->url()})");
            }
            $mail->line('Each link can be used '.config('platform.downloads.max_attempts').' times and expires on '.$downloads->first()->expires_at->format('j F Y').'. If you need a new link, reply to this email with your order reference.');
        }

        if ($order->requires_shipping) {
            $mail->line("Your printed books will be prepared for delivery ({$order->delivery_estimate}). We will email you when they are dispatched.");
        }

        return $mail->action('View your order', $order->customerUrl());
    }
}

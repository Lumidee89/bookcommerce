<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $order = $this->order;
        $mail = (new MailMessage)
            ->subject("Order {$order->reference}: {$order->status->label()}")
            ->greeting("Hello {$order->customer_name},")
            ->line("The status of your order {$order->reference} is now **{$order->status->label()}**.");

        if ($order->shipping_reference) {
            $mail->line('Shipping reference: '.trim("{$order->shipping_carrier} {$order->shipping_reference}"));
        }

        return $mail->action('View your order', $order->customerUrl());
    }
}

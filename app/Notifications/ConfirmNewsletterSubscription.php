<?php

namespace App\Notifications;

use App\Models\NewsletterSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class ConfirmNewsletterSubscription extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public NewsletterSubscriber $subscriber) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = URL::temporarySignedRoute('newsletter.confirm', now()->addDays(7), ['token' => $this->subscriber->token]);

        return (new MailMessage)
            ->subject('Please confirm your subscription')
            ->greeting('One more step')
            ->line('Please confirm that you would like to receive updates from Yemi Adebanjo about new books, articles, teachings and events.')
            ->action('Confirm subscription', $url)
            ->line('If you did not request this, you can ignore this email and you will not be subscribed.');
    }
}

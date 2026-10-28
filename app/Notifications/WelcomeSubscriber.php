<?php

namespace App\Notifications;

use App\Models\NewsletterSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Sent once a newsletter subscription has been confirmed. */
class WelcomeSubscriber extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public NewsletterSubscriber $subscriber) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome — you are subscribed')
            ->greeting($this->subscriber->name ? "Welcome, {$this->subscriber->name}!" : 'Welcome!')
            ->line('Thank you for subscribing to updates from Yemi Adebanjo. You will receive occasional emails about new books, articles, teachings and events.')
            ->action('Explore the books', route('books.index'))
            ->line('You can unsubscribe at any time using the link at the bottom of every newsletter, or here: '.$this->subscriber->unsubscribeUrl());
    }
}

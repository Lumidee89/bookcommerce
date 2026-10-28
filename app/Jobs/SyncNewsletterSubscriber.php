<?php

namespace App\Jobs;

use App\Enums\SubscriberStatus;
use App\Models\NewsletterSubscriber;
use App\Services\Newsletter\NewsletterProvider;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncNewsletterSubscriber implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public array $backoff = [60, 300, 900];

    public function __construct(public NewsletterSubscriber $subscriber) {}

    public function handle(NewsletterProvider $provider): void
    {
        if (! $provider->isConfigured()) {
            return;
        }

        if ($this->subscriber->status === SubscriberStatus::Subscribed) {
            $id = $provider->subscribe($this->subscriber->load('interests'));
            $this->subscriber->update(['provider_id' => $id, 'provider_synced_at' => now()]);
        } elseif ($this->subscriber->status === SubscriberStatus::Unsubscribed) {
            $provider->unsubscribe($this->subscriber);
            $this->subscriber->update(['provider_synced_at' => now()]);
        }
    }
}

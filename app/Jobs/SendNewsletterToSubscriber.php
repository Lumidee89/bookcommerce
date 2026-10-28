<?php

namespace App\Jobs;

use App\Enums\SubscriberStatus;
use App\Mail\NewsletterMail;
use App\Models\NewsletterDelivery;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class SendNewsletterToSubscriber implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [60, 300];

    public function __construct(public NewsletterDelivery $delivery) {}

    public function handle(): void
    {
        $delivery = $this->delivery->fresh(['campaign', 'subscriber']);
        if (! $delivery || $delivery->status === 'sent') {
            return;
        }

        // Someone who unsubscribed after the campaign was queued must not receive it.
        if ($delivery->subscriber->status !== SubscriberStatus::Subscribed) {
            $delivery->update(['status' => 'skipped']);

            return;
        }

        Mail::to($delivery->subscriber->email)->send(new NewsletterMail($delivery->campaign, $delivery->subscriber));

        $delivery->update(['status' => 'sent', 'sent_at' => now(), 'error' => null]);
        $delivery->campaign()->increment('sent_count');
        $this->markCampaignSentIfDone();
    }

    public function failed(Throwable $e): void
    {
        $this->delivery->update(['status' => 'failed', 'error' => Str::limit($e->getMessage(), 250)]);
        $this->delivery->campaign()->increment('failed_count');
        $this->markCampaignSentIfDone();
    }

    private function markCampaignSentIfDone(): void
    {
        $campaign = $this->delivery->campaign()->first();
        if ($campaign->deliveries()->where('status', 'queued')->doesntExist()) {
            $campaign->update(['status' => 'sent', 'sent_at' => $campaign->sent_at ?? now()]);
        }
    }
}

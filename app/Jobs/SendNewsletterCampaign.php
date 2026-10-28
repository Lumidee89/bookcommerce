<?php

namespace App\Jobs;

use App\Models\NewsletterCampaign;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/** Fans a campaign out into one queued job per recipient. */
class SendNewsletterCampaign implements ShouldQueue
{
    use Queueable;

    public function __construct(public NewsletterCampaign $campaign) {}

    public function handle(): void
    {
        $campaign = $this->campaign;
        $delay = 0;

        $campaign->audience()->select('newsletter_subscribers.id')->chunkById(200, function ($subscribers) use ($campaign, &$delay) {
            foreach ($subscribers as $subscriber) {
                $delivery = $campaign->deliveries()->firstOrCreate(['newsletter_subscriber_id' => $subscriber->id]);
                if ($delivery->wasRecentlyCreated) {
                    // Spread sends out (about 1 per second) so the SMTP server isn't flooded.
                    SendNewsletterToSubscriber::dispatch($delivery)->delay(now()->addSeconds($delay++));
                }
            }
        });

        $campaign->update(['recipients_count' => $campaign->deliveries()->count()]);
    }
}

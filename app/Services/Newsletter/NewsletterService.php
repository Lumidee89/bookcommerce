<?php

namespace App\Services\Newsletter;

use App\Enums\SubscriberStatus;
use App\Jobs\SyncNewsletterSubscriber;
use App\Models\NewsletterSubscriber;
use App\Notifications\ConfirmNewsletterSubscription;
use App\Notifications\WelcomeSubscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class NewsletterService
{
    public const CONSENT_TEXT = 'I agree to receive occasional emails from Yemi Adebanjo about new books, articles, teachings and events. I can unsubscribe at any time.';

    /**
     * Start a double opt-in subscription. The response is identical whether or not the
     * address already exists, so the form cannot be used to discover subscribers.
     *
     * @param  list<int>  $interestIds
     */
    public function subscribe(string $email, ?string $name, array $interestIds, Request $request, string $source = 'website'): void
    {
        $subscriber = NewsletterSubscriber::firstOrNew(['email' => strtolower($email)]);

        if ($subscriber->exists && $subscriber->status === SubscriberStatus::Subscribed) {
            $subscriber->interests()->syncWithoutDetaching($interestIds);

            return;
        }

        $subscriber->fill([
            'name' => $name ?: $subscriber->name,
            'status' => SubscriberStatus::Pending,
            'token' => $subscriber->token ?: Str::random(48),
            'source' => $source,
            'unsubscribed_at' => null,
        ])->save();

        $subscriber->interests()->sync($interestIds);
        $this->recordConsent($subscriber, 'subscribe_requested', $request);

        Notification::route('mail', $subscriber->email)->notify(new ConfirmNewsletterSubscription($subscriber));
    }

    public function confirm(NewsletterSubscriber $subscriber, Request $request): void
    {
        if ($subscriber->status === SubscriberStatus::Subscribed) {
            return;
        }

        $subscriber->update(['status' => SubscriberStatus::Subscribed, 'confirmed_at' => now(), 'unsubscribed_at' => null]);
        $this->recordConsent($subscriber, 'confirmed', $request);

        Notification::route('mail', $subscriber->email)->notify(new WelcomeSubscriber($subscriber));
        SyncNewsletterSubscriber::dispatch($subscriber);
    }

    public function unsubscribe(NewsletterSubscriber $subscriber, ?Request $request = null): void
    {
        if ($subscriber->status === SubscriberStatus::Unsubscribed) {
            return;
        }

        $subscriber->update(['status' => SubscriberStatus::Unsubscribed, 'unsubscribed_at' => now()]);
        $this->recordConsent($subscriber, 'unsubscribed', $request);

        SyncNewsletterSubscriber::dispatch($subscriber);
    }

    private function recordConsent(NewsletterSubscriber $subscriber, string $action, ?Request $request): void
    {
        $subscriber->consents()->create([
            'action' => $action,
            'consent_text' => $action === 'subscribe_requested' ? self::CONSENT_TEXT : null,
            'ip_address' => $request?->ip(),
            'user_agent' => Str::limit((string) $request?->userAgent(), 250, ''),
            'created_at' => now(),
        ]);
    }
}

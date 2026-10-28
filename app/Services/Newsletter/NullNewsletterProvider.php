<?php

namespace App\Services\Newsletter;

use App\Models\NewsletterSubscriber;

/** Subscribers are kept locally until a marketing provider is chosen and configured. */
class NullNewsletterProvider implements NewsletterProvider
{
    public function name(): string
    {
        return 'none';
    }

    public function isConfigured(): bool
    {
        return false;
    }

    public function subscribe(NewsletterSubscriber $subscriber): ?string
    {
        return null;
    }

    public function unsubscribe(NewsletterSubscriber $subscriber): void {}
}

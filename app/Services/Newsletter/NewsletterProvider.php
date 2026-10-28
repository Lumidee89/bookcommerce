<?php

namespace App\Services\Newsletter;

use App\Models\NewsletterSubscriber;

interface NewsletterProvider
{
    public function name(): string;

    public function isConfigured(): bool;

    /** Push a confirmed subscriber to the marketing platform; returns the provider's member id. */
    public function subscribe(NewsletterSubscriber $subscriber): ?string;

    public function unsubscribe(NewsletterSubscriber $subscriber): void;
}

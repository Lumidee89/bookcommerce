<?php

namespace App\Services\Newsletter;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Mailchimp Marketing API v3. Consent is collected and confirmed on this site (double opt-in),
 * so members are written with status "subscribed".
 */
class MailchimpProvider implements NewsletterProvider
{
    public function __construct(private readonly array $config) {}

    public function name(): string
    {
        return 'mailchimp';
    }

    public function isConfigured(): bool
    {
        return filled($this->config['api_key'] ?? null) && filled($this->config['list_id'] ?? null) && filled($this->config['server_prefix'] ?? null);
    }

    public function subscribe(NewsletterSubscriber $subscriber): ?string
    {
        $response = $this->http()->put($this->memberPath($subscriber), [
            'email_address' => $subscriber->email,
            'status_if_new' => 'subscribed',
            'status' => 'subscribed',
            'merge_fields' => array_filter(['FNAME' => $subscriber->name]),
        ]);

        if (! $response->successful()) {
            throw new RuntimeException('Mailchimp sync failed: '.$response->json('detail', $response->status()));
        }

        if ($tags = $subscriber->interests->pluck('name')->all()) {
            $this->http()->post($this->memberPath($subscriber).'/tags', [
                'tags' => array_map(fn ($t) => ['name' => $t, 'status' => 'active'], $tags),
            ]);
        }

        return $response->json('id');
    }

    public function unsubscribe(NewsletterSubscriber $subscriber): void
    {
        $this->http()->patch($this->memberPath($subscriber), ['status' => 'unsubscribed']);
    }

    private function memberPath(NewsletterSubscriber $subscriber): string
    {
        return "/lists/{$this->config['list_id']}/members/".md5(strtolower($subscriber->email));
    }

    private function http(): PendingRequest
    {
        return Http::baseUrl("https://{$this->config['server_prefix']}.api.mailchimp.com/3.0")
            ->withBasicAuth('anystring', $this->config['api_key'])
            ->acceptJson()
            ->timeout(15);
    }
}

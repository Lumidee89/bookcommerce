<?php

namespace App\Mail;

use App\Models\NewsletterCampaign;
use App\Models\NewsletterSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class NewsletterMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public NewsletterCampaign $campaign, public ?NewsletterSubscriber $subscriber = null) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->campaign->subject);
    }

    /** One-click unsubscribe headers, which mailbox providers expect on bulk mail. */
    public function headers(): Headers
    {
        if (! $this->subscriber) {
            return new Headers;
        }

        return new Headers(text: [
            'List-Unsubscribe' => '<'.$this->subscriber->unsubscribeUrl().'>',
            'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
        ]);
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.newsletter', with: [
            'bodyHtml' => $this->campaign->bodyHtml(),
            'preheader' => $this->campaign->preheader,
            'name' => $this->subscriber?->name,
            'unsubscribeUrl' => $this->subscriber?->unsubscribeUrl() ?? url('/'),
        ]);
    }
}

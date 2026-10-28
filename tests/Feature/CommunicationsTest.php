<?php

namespace Tests\Feature;

use App\Enums\SubscriberStatus;
use App\Models\ContactEnquiry;
use App\Models\NewsletterSubscriber;
use App\Models\SpeakingInvitation;
use App\Notifications\ConfirmNewsletterSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class CommunicationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    public function test_contact_enquiry_is_recorded()
    {
        $this->post('/contact', ['category' => 'media', 'name' => 'Ola', 'email' => 'ola@example.com', 'subject' => 'Interview', 'message' => 'Hello'])->assertSessionHas('success');
        $this->assertSame('media', ContactEnquiry::sole()->category->value);
    }

    public function test_honeypot_rejects_bots()
    {
        $this->post('/contact', ['category' => 'media', 'name' => 'Bot', 'email' => 'bot@example.com', 'subject' => 'x', 'message' => 'x', 'website' => 'spam'])->assertSessionHasErrors('website');
        $this->assertSame(0, ContactEnquiry::count());
    }

    public function test_speaking_invitation_is_recorded_as_new()
    {
        $this->post('/speaking', ['contact_name' => 'Tunde', 'organisation' => 'Church', 'email' => 't@example.com', 'event_name' => 'Summit'])->assertSessionHas('success');
        $this->assertSame('new', SpeakingInvitation::sole()->status->value);
    }

    public function test_newsletter_requires_consent_and_double_opt_in()
    {
        $this->post('/newsletter', ['email' => 'reader@example.com'])->assertSessionHasErrors('consent');

        $this->post('/newsletter', ['email' => 'reader@example.com', 'consent' => true])->assertSessionHas('success');
        $subscriber = NewsletterSubscriber::sole();
        $this->assertSame(SubscriberStatus::Pending, $subscriber->status);
        Notification::assertSentOnDemand(ConfirmNewsletterSubscription::class);

        $this->get("/newsletter/confirm/{$subscriber->token}")->assertForbidden();
        $this->get(URL::temporarySignedRoute('newsletter.confirm', now()->addDay(), ['token' => $subscriber->token]))->assertOk();
        $this->assertSame(SubscriberStatus::Subscribed, $subscriber->fresh()->status);

        $this->post("/newsletter/unsubscribe/{$subscriber->token}");
        $this->assertSame(SubscriberStatus::Unsubscribed, $subscriber->fresh()->status);
        $this->assertSame(3, $subscriber->consents()->count());
    }

    public function test_contacting_does_not_subscribe_anyone()
    {
        $this->post('/contact', ['category' => 'general', 'name' => 'Ola', 'email' => 'ola@example.com', 'subject' => 'Hi', 'message' => 'Hello']);
        $this->assertSame(0, NewsletterSubscriber::count());
    }
}

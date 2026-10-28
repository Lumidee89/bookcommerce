<?php

namespace Tests\Feature;

use App\Mail\NewsletterMail;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterInterest;
use App\Models\NewsletterSubscriber;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\DashboardAccountCreated;
use App\Notifications\OrderPaid;
use App\Notifications\WelcomeSubscriber;
use App\Services\Payments\TestGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Tests\TestCase;

class EmailTest extends TestCase
{
    use RefreshDatabase;

    private function subscriber(string $email, string $status = 'subscribed', array $interests = []): NewsletterSubscriber
    {
        $s = NewsletterSubscriber::create(['email' => $email, 'status' => $status, 'token' => Str::random(48)]);
        $s->interests()->sync($interests);

        return $s;
    }

    public function test_buyer_receives_receipt_after_verified_payment()
    {
        Notification::fake();
        $book = $this->makeBook(['pdf' => [500000]]);
        $this->post('/cart', ['format_id' => $this->format($book, 'pdf')->id]);
        $this->post('/checkout', ['name' => 'Ada', 'email' => 'ada@example.com', 'accept_terms' => true]);

        Notification::assertNothingSent();

        $tx = Transaction::sole();
        app(TestGateway::class)->simulate($tx, 'success');
        $this->get('/payments/callback?reference='.$tx->reference);

        Notification::assertSentOnDemand(OrderPaid::class, fn ($n, $channels, $notifiable) => array_key_exists('ada@example.com', $notifiable->routes['mail']));
    }

    public function test_confirmed_subscriber_gets_a_welcome_email()
    {
        Notification::fake();
        $s = $this->subscriber('reader@example.com', 'pending');

        $this->get(URL::temporarySignedRoute('newsletter.confirm', now()->addDay(), ['token' => $s->token]))->assertOk();

        Notification::assertSentOnDemand(WelcomeSubscriber::class);
    }

    public function test_new_dashboard_user_is_emailed_a_set_password_link()
    {
        Notification::fake();
        $this->actingAs($this->userWithRole('owner'));

        $this->post('/admin/users', ['name' => 'Team Member', 'email' => 'team@example.com', 'roles' => ['content-manager']])->assertRedirect();

        Notification::assertSentTo(User::where('email', 'team@example.com')->sole(), DashboardAccountCreated::class);
    }

    public function test_newsletter_goes_only_to_confirmed_subscribers_in_selected_groups()
    {
        Mail::fake();
        $books = NewsletterInterest::create(['name' => 'Books']);
        $events = NewsletterInterest::create(['name' => 'Events']);
        $this->subscriber('books@example.com', 'subscribed', [$books->id]);
        $this->subscriber('events@example.com', 'subscribed', [$events->id]);
        $this->subscriber('pending@example.com', 'pending', [$books->id]);
        $this->subscriber('gone@example.com', 'unsubscribed', [$books->id]);

        $this->actingAs($this->userWithRole('owner'));
        $this->post('/admin/newsletters', ['subject' => 'New book', 'body' => 'Hello **readers**', 'interest_ids' => [$books->id]]);
        $campaign = NewsletterCampaign::sole();

        $this->post("/admin/newsletters/{$campaign->id}/send")->assertSessionHas('success');

        Mail::assertSent(NewsletterMail::class, 1);
        Mail::assertSent(NewsletterMail::class, fn ($m) => $m->hasTo('books@example.com'));
        $this->assertSame('sent', $campaign->fresh()->status);

        // Sending twice is refused.
        $this->post("/admin/newsletters/{$campaign->id}/send")->assertStatus(422);
        Mail::assertSent(NewsletterMail::class, 1);
    }

    public function test_newsletter_email_has_unsubscribe_link_and_header()
    {
        $s = $this->subscriber('r@example.com');
        $campaign = NewsletterCampaign::create(['subject' => 'Hi', 'body' => 'Body']);
        $mail = new NewsletterMail($campaign, $s);

        $mail->assertSeeInHtml($s->unsubscribeUrl());
        $this->assertSame('<'.$s->unsubscribeUrl().'>', $mail->headers()->text['List-Unsubscribe']);
    }

    public function test_content_manager_cannot_send_newsletters()
    {
        $this->actingAs($this->userWithRole('content-manager'));
        $this->get('/admin/newsletters')->assertForbidden();
    }
}

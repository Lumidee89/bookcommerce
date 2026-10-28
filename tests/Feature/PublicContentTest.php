<?php

namespace Tests\Feature;

use App\Models\Award;
use App\Models\Event;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_render()
    {
        foreach (['/', '/about', '/books', '/consultations', '/videos', '/articles', '/events', '/gallery', '/speaking', '/contact', '/cart', '/sitemap.xml'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_unapproved_awards_and_testimonials_stay_private()
    {
        Award::create(['category' => 'award', 'title' => 'Unverified Award', 'is_approved' => false]);
        Testimonial::create(['name' => 'No Permission', 'body' => 'Great', 'status' => 'approved', 'permission_granted' => false]);

        $this->get('/about')->assertDontSee('Unverified Award')->assertDontSee('No Permission');
    }

    public function test_private_events_never_appear()
    {
        Event::create(['title' => 'Confidential Board Retreat', 'starts_at' => now()->addWeek(), 'visibility' => 'private', 'is_published' => true, 'status' => 'scheduled']);

        $this->get('/events')->assertDontSee('Confidential Board Retreat');
        $this->get('/events/confidential-board-retreat')->assertNotFound();
    }

    public function test_seo_metadata_is_rendered_server_side()
    {
        $this->get('/books')->assertSee('<meta name="description"', false)->assertSee('Books — Yemi Adebanjo', false);
    }
}

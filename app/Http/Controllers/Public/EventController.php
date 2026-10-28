<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Support\Seo;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('public/events/index', [
            'upcoming' => Event::visible()->upcoming()->get()->map->toCardArray(),
            'past' => Event::visible()->past()->take(24)->get()->map->toCardArray(),
            'seo' => Seo::make('Events & Appearances', 'Upcoming and past events, conferences and appearances featuring Yemi Adebanjo.'),
        ]);
    }

    public function show(Event $event): Response
    {
        abort_unless($event->is_published && $event->visibility === 'public', 404);

        return Inertia::render('public/events/show', [
            'event' => $event->toDetailArray(),
            'videos' => $event->videos()->live()->get()->map->toCardArray(),
            'albums' => $event->albums()->published()->with(['coverPhoto', 'photos'])->withCount('photos')->get()->map->toCardArray(),
            'articles' => $event->articles()->live()->get()->map->toCardArray(),
            'seo' => Seo::make($event->title, $event->summary, $event->toCardArray()['image_url'], 'website', array_filter([
                '@context' => 'https://schema.org',
                '@type' => 'Event',
                'name' => $event->title,
                'startDate' => $event->starts_at->toIso8601String(),
                'endDate' => $event->ends_at?->toIso8601String(),
                'eventAttendanceMode' => $event->is_online ? 'https://schema.org/OnlineEventAttendanceMode' : 'https://schema.org/OfflineEventAttendanceMode',
                'location' => $event->venue ? ['@type' => 'Place', 'name' => $event->venue, 'address' => $event->location] : null,
            ])),
        ]);
    }
}

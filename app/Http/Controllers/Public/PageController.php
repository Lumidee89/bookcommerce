<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Award;
use App\Models\Page;
use App\Models\ProfileSection;
use App\Models\Setting;
use App\Models\SpeakerDocument;
use App\Models\Testimonial;
use App\Support\Seo;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    public function about(): Response
    {
        $awards = Award::approved()->get();

        return Inertia::render('public/about', [
            'bioShort' => Setting::get('bio.short'),
            'sections' => ProfileSection::published()->get()->map->toPublicArray(),
            'awardGroups' => $awards->groupBy(fn (Award $a) => $a->category->value)
                ->map(fn ($group) => ['label' => $group->first()->category->label(), 'items' => $group->map->toPublicArray()->values()])
                ->values(),
            'documents' => SpeakerDocument::published()->get()->map->toPublicArray(),
            'testimonials' => Testimonial::public()->take(6)->get()->map->toPublicArray(),
            'seo' => Seo::make('About Yemi Adebanjo', Setting::get('bio.short')),
        ]);
    }

    public function show(Page $page): Response
    {
        abort_unless($page->is_published, 404);

        return Inertia::render('public/page', [
            'page' => ['title' => $page->title, 'body_html' => $page->bodyHtml(), 'updated_at' => $page->updated_at->toIso8601String()],
            'seo' => Seo::make($page->seo_title ?: $page->title, $page->seo_description),
        ]);
    }
}

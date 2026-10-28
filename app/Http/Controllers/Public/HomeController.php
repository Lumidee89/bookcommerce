<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Album;
use App\Models\Article;
use App\Models\Award;
use App\Models\Book;
use App\Models\ConsultationService;
use App\Models\Event;
use App\Models\Setting;
use App\Models\Testimonial;
use App\Models\Video;
use App\Support\Seo;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(): Response
    {
        $currency = $this->activeCurrency();

        return Inertia::render('public/home', [
            'hero' => [
                'title' => Setting::get('home.hero_title', 'Yemi Adebanjo'),
                'intro' => Setting::get('home.hero_intro'),
                'image_url' => Setting::get('home.hero_image') ? asset('storage/'.Setting::get('home.hero_image')) : null,
            ],
            'bioPreview' => Setting::get('bio.short'),
            'books' => Book::published()->with(['formats.prices'])->where('is_featured', true)->orderBy('sort_order')->take(4)->get()
                ->map->toCardArray($currency),
            'services' => ConsultationService::published()->take(5)->get()->map->toCardArray(),
            'featuredVideo' => Video::live()->with('category')->where('is_featured', true)->latest('published_at')->first()?->toDetailArray(),
            'articles' => Article::live()->with('category')->latest('published_at')->take(3)->get()->map->toCardArray(),
            'events' => Event::visible()->upcoming()->take(3)->get()->map->toCardArray(),
            'awards' => Award::approved()->where('is_featured', true)->take(4)->get()->map->toPublicArray(),
            'testimonials' => Testimonial::public()->where('is_featured', true)->take(3)->get()->map->toPublicArray(),
            'albums' => Album::published()->with(['coverPhoto', 'photos'])->withCount('photos')->take(3)->get()->map->toCardArray(),
            'seo' => Seo::make(
                description: Setting::get('home.seo_description', 'Yemi Adebanjo — author, leadership teacher and consultant. Books, consultations, teachings and speaking engagements.'),
                jsonLd: [
                    '@context' => 'https://schema.org',
                    '@type' => 'Person',
                    'name' => 'Yemi Adebanjo',
                    'url' => url('/'),
                    'image' => asset('images/brand/logo-original.jpg'),
                    'sameAs' => array_values(array_filter(Setting::get('social', []))),
                ],
            ),
        ]);
    }
}

<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Album;
use App\Models\Article;
use App\Models\Book;
use App\Models\ConsultationService;
use App\Models\Event;
use App\Models\Page;
use App\Models\Video;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = collect(['home', 'about', 'books.index', 'consultations.index', 'videos.index', 'articles.index', 'events.index', 'gallery.index', 'speaking', 'contact'])
            ->map(fn ($name) => ['loc' => route($name), 'lastmod' => null]);

        $add = fn ($models, string $route) => $models->each(fn ($m) => $urls->push(['loc' => route($route, $m), 'lastmod' => $m->updated_at?->toAtomString()]));

        $add(Book::published()->get(['id', 'slug', 'updated_at']), 'books.show');
        $add(Article::live()->get(['id', 'slug', 'updated_at']), 'articles.show');
        $add(Video::live()->get(['id', 'slug', 'updated_at']), 'videos.show');
        $add(Event::visible()->get(['id', 'slug', 'updated_at']), 'events.show');
        $add(Album::published()->get(['id', 'slug', 'updated_at']), 'gallery.show');
        $add(ConsultationService::published()->get(['id', 'slug', 'updated_at']), 'consultations.show');
        $add(Page::where('is_published', true)->get(['id', 'slug', 'updated_at']), 'pages.show');

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach ($urls as $url) {
            $xml .= '<url><loc>'.e($url['loc']).'</loc>'.($url['lastmod'] ? '<lastmod>'.$url['lastmod'].'</lastmod>' : '').'</url>';
        }
        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}

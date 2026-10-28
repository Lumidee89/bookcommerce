<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Album;
use App\Support\Seo;
use Inertia\Inertia;
use Inertia\Response;

class GalleryController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('public/gallery/index', [
            'albums' => Album::published()->with(['coverPhoto', 'photos'])->withCount('photos')->get()->map->toCardArray(),
            'seo' => Seo::make('Gallery', 'Photographs from events, ministry and speaking engagements.'),
        ]);
    }

    public function show(Album $album): Response
    {
        abort_unless($album->is_published, 404);

        return Inertia::render('public/gallery/show', [
            'album' => $album->toCardArray(),
            'photos' => $album->photos->map->toPublicArray(),
            'seo' => Seo::make($album->title, $album->description, $album->coverUrl()),
        ]);
    }
}

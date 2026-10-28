<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Models\VideoCategory;
use App\Support\Seo;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VideoController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:100'], 'category' => ['nullable', 'string', 'max:100']]);

        $videos = Video::live()->with('category')
            ->when($filters['q'] ?? null, fn ($q, $term) => $q->where(fn ($q) => $q->where('title', 'like', "%{$term}%")->orWhere('description', 'like', "%{$term}%")))
            ->when($filters['category'] ?? null, fn ($q, $slug) => $q->whereHas('category', fn ($q) => $q->where('slug', $slug)))
            ->latest('published_at')
            ->paginate(12)->withQueryString()
            ->through(fn ($item) => $item->toCardArray());

        return Inertia::render('public/videos/index', [
            'videos' => $videos,
            'featured' => empty($filters) ? Video::live()->with('category')->where('is_featured', true)->latest('published_at')->first()?->toDetailArray() : null,
            'categories' => VideoCategory::orderBy('sort_order')->get(['name', 'slug']),
            'filters' => $filters,
            'seo' => Seo::make('Videos', 'Teachings, interviews and speaking engagements by Yemi Adebanjo.'),
        ]);
    }

    public function show(Video $video): Response
    {
        abort_unless(Video::live()->whereKey($video->id)->exists(), 404);
        $video->load('category');

        return Inertia::render('public/videos/show', [
            'video' => $video->toDetailArray(),
            'related' => Video::live()->with('category')->whereKeyNot($video->id)
                ->when($video->video_category_id, fn ($q) => $q->where('video_category_id', $video->video_category_id))
                ->latest('published_at')->take(3)->get()->map->toCardArray(),
            'seo' => Seo::make($video->title, $video->description, $video->thumbnailUrl(), 'video.other'),
        ]);
    }
}

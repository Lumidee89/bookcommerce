<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Support\Seo;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ArticleController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:100'], 'category' => ['nullable', 'string', 'max:100'], 'tag' => ['nullable', 'string', 'max:100']]);

        $articles = Article::live()->with('category')
            ->when($filters['q'] ?? null, fn ($q, $term) => $q->where(fn ($q) => $q->where('title', 'like', "%{$term}%")->orWhere('summary', 'like', "%{$term}%")))
            ->when($filters['category'] ?? null, fn ($q, $slug) => $q->whereHas('category', fn ($q) => $q->where('slug', $slug)))
            ->when($filters['tag'] ?? null, fn ($q, $slug) => $q->whereHas('tags', fn ($q) => $q->where('slug', $slug)))
            ->latest('published_at')
            ->paginate(9)->withQueryString()
            ->through(fn ($item) => $item->toCardArray());

        return Inertia::render('public/articles/index', [
            'articles' => $articles,
            'categories' => ArticleCategory::orderBy('sort_order')->get(['name', 'slug']),
            'filters' => $filters,
            'seo' => Seo::make('Articles & Teachings', 'Articles, teachings, announcements and professional updates from Yemi Adebanjo.'),
        ]);
    }

    public function show(Request $request, Article $article): Response
    {
        $canPreview = $request->user()?->can('manage articles');
        abort_unless($article->isLive() || $canPreview, 404);
        $article->load(['category', 'tags']);

        $related = Article::live()->with('category')->whereKeyNot($article->id)
            ->where('article_category_id', $article->article_category_id)->latest('published_at')->take(3)->get()->map->toCardArray();

        return Inertia::render('public/articles/show', [
            'article' => $article->toDetailArray(),
            'related' => $related,
            'isPreview' => ! $article->isLive(),
            'seo' => Seo::make($article->seo_title ?: $article->title, $article->seo_description ?: $article->summary, $article->toCardArray()['image_url'], 'article', [
                '@context' => 'https://schema.org',
                '@type' => 'Article',
                'headline' => $article->title,
                'datePublished' => $article->published_at?->toIso8601String(),
                'dateModified' => $article->updated_at->toIso8601String(),
                'author' => ['@type' => 'Person', 'name' => $article->author_name ?: 'Yemi Adebanjo'],
            ]),
        ]);
    }
}

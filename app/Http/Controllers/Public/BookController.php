<?php

namespace App\Http\Controllers\Public;

use App\Enums\SalesStatus;
use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookCategory;
use App\Support\Seo;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BookController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
            'format' => ['nullable', 'in:digital,print'],
            'availability' => ['nullable', 'in:available,forthcoming'],
        ]);
        $currency = $this->activeCurrency();

        $books = Book::published()
            ->with(['formats.prices'])
            ->when($filters['q'] ?? null, fn ($q, $term) => $q->where(fn ($q) => $q->where('title', 'like', "%{$term}%")
                ->orWhere('subtitle', 'like', "%{$term}%")->orWhere('topics', 'like', "%{$term}%")))
            ->when($filters['category'] ?? null, fn ($q, $slug) => $q->whereHas('categories', fn ($q) => $q->where('slug', $slug)))
            ->when($filters['format'] ?? null, fn ($q, $format) => $q->whereHas('formats', fn ($q) => $q->where('is_active', true)->where('is_digital', $format === 'digital')))
            ->when(($filters['availability'] ?? null) === 'available', fn ($q) => $q->whereIn('sales_status', [SalesStatus::Available, SalesStatus::Free]))
            ->when(($filters['availability'] ?? null) === 'forthcoming', fn ($q) => $q->where('sales_status', SalesStatus::Forthcoming))
            ->orderByDesc('is_featured')->orderBy('sort_order')->orderByDesc('published_on')
            ->paginate(12)->withQueryString()
            ->through(fn (Book $book) => $book->toCardArray($currency));

        return Inertia::render('public/books/index', [
            'books' => $books,
            'categories' => BookCategory::orderBy('sort_order')->orderBy('name')->get(['name', 'slug']),
            'filters' => $filters,
            'seo' => Seo::make('Books', 'Books and publications by Yemi Adebanjo, available in digital and print formats.'),
        ]);
    }

    public function show(Book $book): Response
    {
        abort_unless($book->publication_status->value === 'published', 404);
        $currency = $this->activeCurrency();
        $book->load(['formats.prices', 'categories']);

        $related = Book::published()->with('formats.prices')->whereKeyNot($book->id)
            ->whereHas('categories', fn ($q) => $q->whereIn('book_categories.id', $book->categories->pluck('id')))
            ->take(4)->get()->map->toCardArray($currency);

        // Structured data only includes facts we actually hold (no invented ISBNs or ratings).
        $jsonLd = array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Book',
            'name' => $book->title,
            'author' => ['@type' => 'Person', 'name' => $book->author],
            'image' => $book->coverUrl(),
            'isbn' => $book->isbn,
            'publisher' => $book->publisher,
            'datePublished' => $book->published_on?->toDateString(),
            'url' => route('books.show', $book),
        ]);

        return Inertia::render('public/books/show', [
            'book' => $book->toDetailArray($currency),
            'related' => $related,
            'seo' => Seo::make($book->seo_title ?: $book->title, $book->seo_description ?: $book->description, $book->coverUrl(), 'book', $jsonLd),
        ]);
    }
}

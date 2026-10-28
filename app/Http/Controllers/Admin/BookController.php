<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Column;
use App\Admin\Field;
use App\Admin\ResourceController;
use App\Enums\BookFormatType;
use App\Enums\PublicationStatus;
use App\Enums\SalesStatus;
use App\Models\Book;
use App\Models\BookCategory;
use App\Models\BookFormat;
use App\Support\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class BookController extends ResourceController
{
    protected string $model = Book::class;

    protected string $routeName = 'admin.books';

    protected string $title = 'Books';

    protected string $singular = 'Book';

    protected array $with = ['formats.prices', 'categories'];

    protected array $searchable = ['title', 'subtitle', 'isbn'];

    protected function description(): ?string
    {
        return 'Publication status controls visibility; sales status controls whether a book can be bought. Forthcoming and display-only books are never purchasable.';
    }

    protected function filters(): array
    {
        return [
            ['name' => 'publication_status', 'label' => 'Publication', 'options' => PublicationStatus::options()],
            ['name' => 'sales_status', 'label' => 'Sales', 'options' => SalesStatus::options()],
        ];
    }

    protected function columns(): array
    {
        return [Column::make('cover', 'Cover', 'image'), Column::make('title', 'Title'), Column::make('publication_status', 'Publication'), Column::make('sales_status', 'Sales'), Column::make('formats_summary', 'Formats'), Column::make('is_featured', 'Featured', 'boolean')];
    }

    protected function row(Model $record): array
    {
        return array_merge(parent::row($record), [
            'cover' => $record->coverUrl(),
            'formats_summary' => $record->formats->map(fn (BookFormat $f) => $f->displayLabel().($f->is_digital ? '' : " ({$f->stock_quantity})"))->implode(', ') ?: '—',
        ]);
    }

    protected function fields(?Model $record): array
    {
        return [
            Field::text('title', 'Title')->required()->half(),
            Field::text('subtitle', 'Subtitle')->half(),
            Field::text('author', 'Author')->half()->set('default', 'Yemi Adebanjo'),
            Field::multiselect('category_ids', 'Categories', BookCategory::orderBy('sort_order')->get()->map(fn ($c) => ['value' => $c->id, 'label' => $c->name])->all())->half(),
            Field::markdown('description', 'Description')->rows(10),
            Field::image('cover_path', 'Cover image')->disk('public', 'books/covers')->half(),
            Field::file('sample_path', 'Sample / preview (PDF)')->disk('public', 'books/samples')->accept('application/pdf')->half()->help('Publicly downloadable. Only upload an authorised excerpt.'),
            Field::tags('topics', 'Topics / keywords')->help('Comma separated.'),
            Field::markdown('table_of_contents', 'Table of contents')->rows(6),
            Field::heading('Publication details'),
            Field::text('isbn', 'ISBN')->half()->help('Only enter a real, assigned ISBN.'),
            Field::text('publisher', 'Publisher')->half(),
            Field::date('published_on', 'Publication date')->half(),
            Field::heading('Status'),
            Field::select('publication_status', 'Publication status', PublicationStatus::options())->required()->half()->set('default', 'draft'),
            Field::select('sales_status', 'Sales status', SalesStatus::options())->required()->half()->set('default', 'display_only'),
            Field::number('sort_order', 'Display order')->half(),
            Field::checkbox('is_featured', 'Feature on homepage'),
            Field::heading('Search engines'),
            Field::text('seo_title', 'SEO title')->half(),
            Field::textarea('seo_description', 'SEO description')->rows(2),
        ];
    }

    protected function rules(?Model $record): array
    {
        return [
            'title' => ['required', 'string', 'max:190'],
            'subtitle' => ['nullable', 'string', 'max:190'],
            'author' => ['required', 'string', 'max:120'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:book_categories,id'],
            'description' => ['nullable', 'string'],
            'cover_path' => self::imageRule(),
            'sample_path' => self::documentRule(),
            'topics' => ['nullable', 'string', 'max:500'],
            'table_of_contents' => ['nullable', 'string'],
            'isbn' => ['nullable', 'string', 'max:20', 'regex:/^[0-9Xx\- ]+$/'],
            'publisher' => ['nullable', 'string', 'max:190'],
            'published_on' => ['nullable', 'date'],
            'publication_status' => ['required', Rule::enum(PublicationStatus::class)],
            'sales_status' => ['required', Rule::enum(SalesStatus::class)],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_featured' => ['boolean'],
            'seo_title' => ['nullable', 'string', 'max:190'],
            'seo_description' => ['nullable', 'string', 'max:500'],
        ];
    }

    protected function readValue(Model $record, Field $field): mixed
    {
        return $field->name === 'category_ids' ? $record->categories->pluck('id')->all() : parent::readValue($record, $field);
    }

    protected function afterSave(Model $record, array $data, Request $request): void
    {
        $record->categories()->sync($data['category_ids'] ?? []);
    }

    protected function extra(?Model $record): ?array
    {
        if (! $record) {
            return null;
        }

        return [
            'type' => 'book-formats',
            'store_url' => route('admin.books.formats.store', $record),
            'types' => BookFormatType::options(),
            'currencies' => config('platform.currencies'),
            'formats' => $record->formats->map(fn (BookFormat $f) => [
                'id' => $f->id,
                'type' => $f->type->value,
                'label' => $f->label,
                'display_label' => $f->displayLabel(),
                'sku' => $f->sku,
                'is_digital' => $f->is_digital,
                'is_active' => $f->is_active,
                'stock_quantity' => $f->stock_quantity,
                'has_file' => $f->file_path && Storage::disk('protected')->exists($f->file_path),
                'file_name' => $f->file_path ? basename($f->file_path) : null,
                'prices' => collect(config('platform.currencies'))->mapWithKeys(fn ($c) => [$c => ($p = $f->priceFor($c)) === null ? '' : number_format($p / 100, 2, '.', '')]),
                'prices_display' => $f->prices->map(fn ($p) => Money::format($p->amount, $p->currency))->implode(' · '),
                'update_url' => route('admin.formats.update', $f),
                'delete_url' => route('admin.formats.destroy', $f),
                'stock_url' => route('admin.formats.stock', $f),
            ]),
        ];
    }

    protected function viewUrl(Model $record): ?string
    {
        return $record->publication_status === PublicationStatus::Published ? route('books.show', $record) : null;
    }
}

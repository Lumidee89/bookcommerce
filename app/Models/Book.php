<?php

namespace App\Models;

use App\Enums\PublicationStatus;
use App\Enums\SalesStatus;
use App\Models\Concerns\HasPublicUrls;
use App\Models\Concerns\HasSlug;
use App\Support\Markdown;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    use HasPublicUrls, HasSlug;

    protected $guarded = ['id'];

    protected $casts = [
        'topics' => 'array',
        'published_on' => 'date',
        'publication_status' => PublicationStatus::class,
        'sales_status' => SalesStatus::class,
        'is_featured' => 'boolean',
    ];

    public function scopePublished(Builder $query): void
    {
        $query->where('publication_status', PublicationStatus::Published);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(BookCategory::class);
    }

    public function formats(): HasMany
    {
        return $this->hasMany(BookFormat::class)->orderBy('sort_order')->orderBy('id');
    }

    /** Forthcoming and display-only books can never be bought (BR-002). */
    public function isPurchasable(): bool
    {
        return $this->publication_status === PublicationStatus::Published
            && in_array($this->sales_status, [SalesStatus::Available, SalesStatus::Free], true);
    }

    public function formatPrice(BookFormat $format, string $currency): ?int
    {
        return $this->sales_status === SalesStatus::Free ? 0 : $format->priceFor($currency);
    }

    public function coverUrl(): ?string
    {
        return self::publicUrl($this->cover_path);
    }

    public function toCardArray(string $currency): array
    {
        $prices = $this->formats->where('is_active', true)
            ->map(fn (BookFormat $f) => $this->formatPrice($f, $currency))->filter(fn ($p) => $p !== null);

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'subtitle' => $this->subtitle,
            'author' => $this->author,
            'cover_url' => $this->coverUrl(),
            'sales_status' => $this->sales_status->value,
            'sales_status_label' => $this->sales_status->label(),
            'is_purchasable' => $this->isPurchasable(),
            'from_price' => $prices->isEmpty() ? null : $prices->min(),
            'currency' => $currency,
            'formats' => $this->formats->where('is_active', true)->map(fn (BookFormat $f) => $f->type->label())->values()->all(),
        ];
    }

    public function toDetailArray(string $currency): array
    {
        return $this->toCardArray($currency) + [
            'description_html' => Markdown::render($this->description),
            'table_of_contents_html' => Markdown::render($this->table_of_contents),
            'topics' => $this->topics ?? [],
            'isbn' => $this->isbn,
            'publisher' => $this->publisher,
            'published_on' => $this->published_on?->toDateString(),
            'sample_url' => self::publicUrl($this->sample_path),
            'categories' => $this->categories->map(fn ($c) => ['name' => $c->name, 'slug' => $c->slug])->all(),
            'format_options' => $this->formats->where('is_active', true)->map(fn (BookFormat $f) => [
                'id' => $f->id,
                'type' => $f->type->value,
                'label' => $f->displayLabel(),
                'is_digital' => $f->is_digital,
                'price' => $this->formatPrice($f, $currency),
                'in_stock' => $f->inStock(),
                'stock_quantity' => $f->is_digital ? null : $f->stock_quantity,
            ])->values()->all(),
        ];
    }
}

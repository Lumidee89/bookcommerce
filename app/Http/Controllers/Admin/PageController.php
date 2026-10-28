<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Column;
use App\Admin\Field;
use App\Admin\ResourceController;
use App\Models\Page;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class PageController extends ResourceController
{
    protected string $model = Page::class;

    protected string $routeName = 'admin.pages';

    protected string $title = 'Pages & Policies';

    protected string $singular = 'Page';

    protected function description(): ?string
    {
        return 'Privacy, terms, delivery, refund and booking policies, plus any other standalone pages. Policies must be reviewed before publishing.';
    }

    protected function columns(): array
    {
        return [Column::make('title', 'Title'), Column::make('slug', 'URL slug'), Column::make('is_published', 'Published', 'boolean'), Column::make('updated_at', 'Updated')];
    }

    protected function fields(?Model $record): array
    {
        return [
            Field::text('title', 'Title')->required()->half(),
            Field::text('slug', 'URL slug')->required()->half()->help('Appears as /pages/your-slug. Footer policy links use: privacy-policy, terms, refund-policy, delivery-policy, booking-policy.'),
            Field::markdown('body', 'Content')->rows(18),
            Field::heading('Search engines'),
            Field::text('seo_title', 'SEO title')->half(),
            Field::textarea('seo_description', 'SEO description')->rows(2),
            Field::checkbox('is_published', 'Published'),
        ];
    }

    protected function rules(?Model $record): array
    {
        return [
            'title' => ['required', 'string', 'max:190'],
            'slug' => ['required', 'alpha_dash', 'max:120', Rule::unique('pages')->ignore($record)],
            'body' => ['nullable', 'string'],
            'seo_title' => ['nullable', 'string', 'max:190'],
            'seo_description' => ['nullable', 'string', 'max:500'],
            'is_published' => ['boolean'],
        ];
    }

    protected function viewUrl(Model $record): ?string
    {
        return route('pages.show', $record);
    }
}

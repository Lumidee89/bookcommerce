<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Column;
use App\Admin\Field;
use App\Admin\ResourceController;
use App\Models\BookCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class BookCategoryController extends ResourceController
{
    protected string $model = BookCategory::class;

    protected string $routeName = 'admin.book-categories';

    protected string $title = 'Book Categories';

    protected string $singular = 'Book category';

    protected array $searchable = ['name'];

    protected string $orderBy = 'sort_order';

    protected string $orderDirection = 'asc';

    protected function columns(): array
    {
        return [Column::make('name', 'Name'), Column::make('slug', 'Slug'), Column::make('sort_order', 'Order')];
    }

    protected function fields(?Model $record): array
    {
        return [
            Field::text('name', 'Name')->required()->half(),
            Field::text('slug', 'Slug')->half()->help('Leave empty to generate from the name.'),
            Field::number('sort_order', 'Display order')->half(),
        ];
    }

    protected function rules(?Model $record): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'alpha_dash', 'max:120', Rule::unique('book_categories')->ignore($record)],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}

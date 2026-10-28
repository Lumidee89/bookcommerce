<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Column;
use App\Admin\Field;
use App\Admin\ResourceController;
use App\Models\ProfileSection;
use Illuminate\Database\Eloquent\Model;

class ProfileSectionController extends ResourceController
{
    protected string $model = ProfileSection::class;

    protected string $routeName = 'admin.profile-sections';

    protected string $title = 'Biography Sections';

    protected string $singular = 'Biography section';

    protected string $orderBy = 'sort_order';

    protected string $orderDirection = 'asc';

    protected function description(): ?string
    {
        return 'Sections of the About page — e.g. Background, Leadership, Ministry, Jolax Global Leadership Center. Only approved, verified information should be published.';
    }

    protected function columns(): array
    {
        return [Column::make('title', 'Title'), Column::make('sort_order', 'Order'), Column::make('is_published', 'Published', 'boolean')];
    }

    protected function fields(?Model $record): array
    {
        return [
            Field::text('title', 'Heading')->required(),
            Field::markdown('body', 'Content')->rows(14),
            Field::image('image_path', 'Photograph')->disk('public', 'profile')->half(),
            Field::text('image_alt', 'Photograph description (alt text)')->half(),
            Field::number('sort_order', 'Display order')->half(),
            Field::checkbox('is_published', 'Published'),
        ];
    }

    protected function rules(?Model $record): array
    {
        return [
            'title' => ['required', 'string', 'max:190'],
            'body' => ['nullable', 'string'],
            'image_path' => self::imageRule(),
            'image_alt' => ['nullable', 'string', 'max:190'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_published' => ['boolean'],
        ];
    }

    protected function viewUrl(Model $record): ?string
    {
        return route('about');
    }
}

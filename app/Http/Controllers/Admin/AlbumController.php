<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Column;
use App\Admin\Field;
use App\Admin\ResourceController;
use App\Models\Album;
use App\Models\Event;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AlbumController extends ResourceController
{
    protected string $model = Album::class;

    protected string $routeName = 'admin.albums';

    protected string $title = 'Photo Albums';

    protected string $singular = 'Album';

    protected function columns(): array
    {
        return [Column::make('title', 'Title'), Column::make('event_date', 'Date', 'date'), Column::make('photos_count', 'Photos'), Column::make('is_published', 'Published', 'boolean')];
    }

    protected function scopeIndex(Builder $query): Builder
    {
        return $query->withCount('photos');
    }

    protected function fields(?Model $record): array
    {
        return [
            Field::text('title', 'Album title')->required(),
            Field::textarea('description', 'Description')->rows(3),
            Field::date('event_date', 'Event date')->half(),
            Field::text('location', 'Location')->half(),
            Field::select('event_id', 'Related event', Event::orderByDesc('starts_at')->get(['id', 'title'])->map(fn ($e) => ['value' => $e->id, 'label' => $e->title])->all())->half(),
            Field::number('sort_order', 'Display order')->half(),
            Field::checkbox('is_published', 'Published'),
        ];
    }

    protected function rules(?Model $record): array
    {
        return [
            'title' => ['required', 'string', 'max:190'],
            'description' => ['nullable', 'string', 'max:2000'],
            'event_date' => ['nullable', 'date'],
            'location' => ['nullable', 'string', 'max:190'],
            'event_id' => ['nullable', 'exists:events,id'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_published' => ['boolean'],
        ];
    }

    protected function extra(?Model $record): ?array
    {
        if (! $record) {
            return null;
        }

        return [
            'type' => 'album-photos',
            'cover_photo_id' => $record->cover_photo_id,
            'upload_url' => route('admin.albums.photos.store', $record),
            'photos' => $record->photos->map(fn (Photo $p) => $p->toPublicArray() + [
                'update_url' => route('admin.photos.update', $p),
                'delete_url' => route('admin.photos.destroy', $p),
                'sort_order' => $p->sort_order,
            ]),
        ];
    }

    protected function viewUrl(Model $record): ?string
    {
        return $record->is_published ? route('gallery.show', $record) : null;
    }
}

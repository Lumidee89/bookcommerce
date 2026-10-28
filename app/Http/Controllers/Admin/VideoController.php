<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Column;
use App\Admin\Field;
use App\Admin\ResourceController;
use App\Models\Event;
use App\Models\Video;
use App\Models\VideoCategory;
use Illuminate\Database\Eloquent\Model;

class VideoController extends ResourceController
{
    protected string $model = Video::class;

    protected string $routeName = 'admin.videos';

    protected string $title = 'Videos';

    protected string $singular = 'Video';

    protected array $with = ['category'];

    protected function description(): ?string
    {
        return 'Videos are embedded from YouTube or Vimeo. Paste the public video link; no files are uploaded to this server.';
    }

    protected function columns(): array
    {
        return [Column::make('title', 'Title'), Column::make('category.name', 'Category'), Column::make('provider', 'Platform'), Column::make('is_featured', 'Featured', 'boolean'), Column::make('is_published', 'Published', 'boolean')];
    }

    protected function fields(?Model $record): array
    {
        return [
            Field::text('title', 'Title')->required(),
            Field::url('url', 'YouTube or Vimeo link')->required()->placeholder('https://www.youtube.com/watch?v=…'),
            Field::select('video_category_id', 'Category', VideoCategory::orderBy('sort_order')->get()->map(fn ($c) => ['value' => $c->id, 'label' => $c->name])->all())->half(),
            Field::text('duration', 'Duration')->half()->placeholder('e.g. 42:10'),
            Field::textarea('description', 'Description')->rows(4),
            Field::image('thumbnail_path', 'Custom thumbnail')->disk('public', 'videos')->help('Optional. YouTube thumbnails are used automatically.'),
            Field::textarea('transcript', 'Transcript / captions text')->rows(6),
            Field::select('event_id', 'Related event', Event::orderByDesc('starts_at')->get(['id', 'title'])->map(fn ($e) => ['value' => $e->id, 'label' => $e->title])->all())->half(),
            Field::datetime('published_at', 'Publication date')->half(),
            Field::checkbox('is_featured', 'Featured video'),
            Field::checkbox('is_published', 'Published'),
        ];
    }

    protected function rules(?Model $record): array
    {
        return [
            'title' => ['required', 'string', 'max:190'],
            'url' => ['required', 'url', 'max:500', function ($attr, $value, $fail) {
                if (Video::parseUrl($value)[0] === null) {
                    $fail('Please use a YouTube or Vimeo video link.');
                }
            }],
            'video_category_id' => ['nullable', 'exists:video_categories,id'],
            'duration' => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string', 'max:5000'],
            'thumbnail_path' => self::imageRule(),
            'transcript' => ['nullable', 'string'],
            'event_id' => ['nullable', 'exists:events,id'],
            'published_at' => ['nullable', 'date'],
            'is_featured' => ['boolean'],
            'is_published' => ['boolean'],
        ];
    }

    protected function viewUrl(Model $record): ?string
    {
        return $record->is_published ? route('videos.show', $record) : null;
    }
}

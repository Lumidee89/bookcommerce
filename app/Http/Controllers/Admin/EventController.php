<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Column;
use App\Admin\Field;
use App\Admin\ResourceController;
use App\Enums\EventStatus;
use App\Models\Event;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class EventController extends ResourceController
{
    protected string $model = Event::class;

    protected string $routeName = 'admin.events';

    protected string $title = 'Events & Appearances';

    protected string $singular = 'Event';

    protected string $orderBy = 'starts_at';

    protected function description(): ?string
    {
        return 'Public events only. Private appointments and confidential engagements should be marked private, and will never appear on the website.';
    }

    protected function columns(): array
    {
        return [Column::make('title', 'Title'), Column::make('starts_at', 'Starts'), Column::make('status', 'Status'), Column::make('visibility', 'Visibility'), Column::make('is_published', 'Published', 'boolean')];
    }

    protected function fields(?Model $record): array
    {
        return [
            Field::text('title', 'Event title')->required(),
            Field::textarea('summary', 'Short summary')->rows(2),
            Field::markdown('description', 'Description')->rows(10),
            Field::datetime('starts_at', 'Starts')->required()->half()->help('Enter in '.config('platform.timezone').' time.'),
            Field::datetime('ends_at', 'Ends')->half(),
            Field::text('timezone', 'Event time zone')->half()->set('default', config('platform.timezone')),
            Field::select('status', 'Status', EventStatus::options())->required()->half()->set('default', 'scheduled'),
            Field::text('venue', 'Venue')->half(),
            Field::text('location', 'City / country')->half(),
            Field::checkbox('is_online', 'Online event'),
            Field::url('online_url', 'Online event link')->half(),
            Field::url('registration_url', 'Registration or enquiry link')->half(),
            Field::image('image_path', 'Promotional image')->disk('public', 'events'),
            Field::select('visibility', 'Visibility', [['value' => 'public', 'label' => 'Public'], ['value' => 'private', 'label' => 'Private — never shown on the website']])->required()->half()->set('default', 'public'),
            Field::checkbox('is_published', 'Published'),
        ];
    }

    protected function rules(?Model $record): array
    {
        return [
            'title' => ['required', 'string', 'max:190'],
            'summary' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'timezone' => ['required', 'timezone:all'],
            'status' => ['required', Rule::enum(EventStatus::class)],
            'venue' => ['nullable', 'string', 'max:190'],
            'location' => ['nullable', 'string', 'max:190'],
            'is_online' => ['boolean'],
            'online_url' => ['nullable', 'url', 'max:500'],
            'registration_url' => ['nullable', 'url', 'max:500'],
            'image_path' => self::imageRule(),
            'visibility' => ['required', 'in:public,private'],
            'is_published' => ['boolean'],
        ];
    }

    protected function viewUrl(Model $record): ?string
    {
        return $record->is_published && $record->visibility === 'public' ? route('events.show', $record) : null;
    }
}

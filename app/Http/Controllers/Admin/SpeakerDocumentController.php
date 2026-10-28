<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Column;
use App\Admin\Field;
use App\Admin\ResourceController;
use App\Models\SpeakerDocument;
use Illuminate\Database\Eloquent\Model;

class SpeakerDocumentController extends ResourceController
{
    protected string $model = SpeakerDocument::class;

    protected string $routeName = 'admin.speaker-documents';

    protected string $title = 'Media Kit & Documents';

    protected string $singular = 'Document';

    protected function description(): ?string
    {
        return 'Public downloads such as the speaker profile or media kit, shown on the About and Speaking pages.';
    }

    protected function columns(): array
    {
        return [Column::make('title', 'Title'), Column::make('is_published', 'Published', 'boolean'), Column::make('updated_at', 'Updated')];
    }

    protected function fields(?Model $record): array
    {
        return [
            Field::text('title', 'Title')->required(),
            Field::textarea('description', 'Description')->rows(3),
            Field::file('file_path', 'File (PDF)')->disk('public', 'documents')->accept('application/pdf')->required(! $record),
            Field::number('sort_order', 'Display order')->half(),
            Field::checkbox('is_published', 'Published'),
        ];
    }

    protected function rules(?Model $record): array
    {
        return [
            'title' => ['required', 'string', 'max:190'],
            'description' => ['nullable', 'string', 'max:1000'],
            'file_path' => $record ? self::documentRule() : ['required', ...array_slice(self::documentRule(), 1)],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_published' => ['boolean'],
        ];
    }
}

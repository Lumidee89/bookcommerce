<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Column;
use App\Admin\Field;
use App\Admin\ResourceController;
use App\Enums\TestimonialStatus;
use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TestimonialController extends ResourceController
{
    protected string $model = Testimonial::class;

    protected string $routeName = 'admin.testimonials';

    protected string $title = 'Testimonials';

    protected string $singular = 'Testimonial';

    protected array $searchable = ['name', 'organisation', 'body'];

    protected function description(): ?string
    {
        return 'Only genuine testimonials that are approved and have recorded publication permission appear on the website.';
    }

    protected function filters(): array
    {
        return [['name' => 'status', 'label' => 'Status', 'options' => TestimonialStatus::options()]];
    }

    protected function columns(): array
    {
        return [Column::make('name', 'Name'), Column::make('organisation', 'Organisation'), Column::make('status', 'Status'), Column::make('permission_granted', 'Permission', 'boolean'), Column::make('is_featured', 'Featured', 'boolean')];
    }

    protected function fields(?Model $record): array
    {
        return [
            Field::text('name', 'Contributor name')->required()->half(),
            Field::text('role', 'Role / title')->half(),
            Field::text('organisation', 'Organisation')->half(),
            Field::textarea('body', 'Testimonial')->required()->rows(6),
            Field::image('photo_path', 'Photograph (with permission)')->disk('public', 'testimonials')->half(),
            Field::url('video_url', 'Video link (with permission)')->half(),
            Field::heading('Permission & approval'),
            Field::checkbox('permission_granted', 'The contributor has given permission to publish this testimonial and any associated media'),
            Field::textarea('permission_notes', 'How permission was obtained')->rows(2)->help('For example: “Email from contributor, 3 March 2026”.'),
            Field::select('status', 'Review status', TestimonialStatus::options())->required()->half(),
            Field::number('sort_order', 'Display order')->half(),
            Field::checkbox('is_featured', 'Feature on homepage'),
        ];
    }

    protected function rules(?Model $record): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'role' => ['nullable', 'string', 'max:120'],
            'organisation' => ['nullable', 'string', 'max:190'],
            'body' => ['required', 'string', 'max:3000'],
            'photo_path' => self::imageRule(),
            'video_url' => ['nullable', 'url', 'max:500'],
            'permission_granted' => ['boolean'],
            'permission_notes' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::enum(TestimonialStatus::class)],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_featured' => ['boolean'],
        ];
    }

    protected function beforeSave(Model $record, array $data, Request $request): void
    {
        if ($record->isDirty('permission_granted')) {
            $record->permission_recorded_at = $record->permission_granted ? now() : null;
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Column;
use App\Admin\Field;
use App\Admin\ResourceController;
use App\Enums\AwardCategory;
use App\Models\Award;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AwardController extends ResourceController
{
    protected string $model = Award::class;

    protected string $routeName = 'admin.awards';

    protected string $title = 'Awards & Credentials';

    protected string $singular = 'Award or credential';

    protected function description(): ?string
    {
        return 'Awards, qualifications, certifications and memberships. Entries appear publicly only after owner approval. Conceal any sensitive identifiers on certificate images before uploading.';
    }

    protected function filters(): array
    {
        return [['name' => 'category', 'label' => 'Category', 'options' => AwardCategory::options()]];
    }

    protected function columns(): array
    {
        return [Column::make('title', 'Title'), Column::make('category', 'Category'), Column::make('issuer', 'Issued by'), Column::make('awarded_on', 'Date', 'date'), Column::make('is_approved', 'Approved', 'boolean')];
    }

    protected function fields(?Model $record): array
    {
        $fields = [
            Field::select('category', 'Category', AwardCategory::options())->required()->half(),
            Field::text('title', 'Title')->required()->half(),
            Field::text('issuer', 'Issuing organisation')->half(),
            Field::date('awarded_on', 'Date awarded')->half(),
            Field::textarea('description', 'Description')->rows(4),
            Field::image('image_path', 'Photograph')->disk('public', 'awards')->half(),
            Field::image('certificate_path', 'Certificate image')->disk('public', 'awards')->half()->help('Blur or crop out ID numbers, signatures and other sensitive details first.'),
            Field::url('url', 'Supporting link'),
            Field::number('sort_order', 'Display order')->half(),
            Field::checkbox('is_featured', 'Feature on homepage'),
        ];

        $fields[] = Field::checkbox('is_approved', 'Approved for publication by the owner')
            ->set('disabled', ! auth()->user()?->can('approve profile content'))
            ->help('Only users with approval permission can change this.');

        return $fields;
    }

    protected function rules(?Model $record): array
    {
        return [
            'category' => ['required', Rule::enum(AwardCategory::class)],
            'title' => ['required', 'string', 'max:190'],
            'issuer' => ['nullable', 'string', 'max:190'],
            'awarded_on' => ['nullable', 'date'],
            'description' => ['nullable', 'string', 'max:3000'],
            'image_path' => self::imageRule(),
            'certificate_path' => self::imageRule(),
            'url' => ['nullable', 'url', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_featured' => ['boolean'],
            'is_approved' => ['boolean'],
        ];
    }

    protected function beforeSave(Model $record, array $data, Request $request): void
    {
        // Approval is an owner decision (BR-012); others cannot change it.
        if (! $request->user()->can('approve profile content')) {
            $record->is_approved = $record->getOriginal('is_approved') ?? false;
        }
    }
}

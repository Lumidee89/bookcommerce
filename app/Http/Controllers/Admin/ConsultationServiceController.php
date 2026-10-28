<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Column;
use App\Admin\Field;
use App\Admin\ResourceController;
use App\Enums\DeliveryMethod;
use App\Models\ConsultationService;
use App\Support\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class ConsultationServiceController extends ResourceController
{
    protected string $model = ConsultationService::class;

    protected string $routeName = 'admin.services';

    protected string $title = 'Consultation Services';

    protected string $singular = 'Service';

    protected array $searchable = ['name'];

    protected string $orderBy = 'sort_order';

    protected string $orderDirection = 'asc';

    protected function columns(): array
    {
        return [Column::make('name', 'Service'), Column::make('duration', 'Duration'), Column::make('fee_display', 'Fee'), Column::make('delivery_method', 'Delivery'), Column::make('is_published', 'Published', 'boolean')];
    }

    protected function row(Model $record): array
    {
        return array_merge(parent::row($record), [
            'duration' => "{$record->duration_minutes} min",
            'fee_display' => $record->fee > 0 ? Money::format($record->fee, $record->currency) : 'Free',
        ]);
    }

    protected function fields(?Model $record): array
    {
        $currencies = array_map(fn ($c) => ['value' => $c, 'label' => $c], config('platform.currencies'));

        return [
            Field::text('name', 'Service name')->required(),
            Field::textarea('summary', 'Short summary')->rows(2),
            Field::markdown('description', 'Full description')->rows(8),
            Field::text('audience', 'Intended audience')->placeholder('e.g. Business owners, ministry leaders'),
            Field::number('duration_minutes', 'Session length (minutes)')->required()->half(),
            Field::select('delivery_method', 'Delivery method', DeliveryMethod::options())->required()->half()->set('default', 'online'),
            Field::money('fee', 'Fee')->half()->help('Set to 0 for a free session. Paid sessions can only be booked once a payment gateway is configured.'),
            Field::select('currency', 'Currency', $currencies)->required()->half()->set('default', 'NGN'),
            Field::textarea('preparation_instructions', 'Preparation instructions')->rows(3),
            Field::textarea('cancellation_policy', 'Cancellation policy')->rows(3),
            Field::textarea('rescheduling_policy', 'Rescheduling policy')->rows(3),
            Field::number('sort_order', 'Display order')->half(),
            Field::checkbox('is_published', 'Published'),
        ];
    }

    protected function rules(?Model $record): array
    {
        return [
            'name' => ['required', 'string', 'max:190'],
            'summary' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'audience' => ['nullable', 'string', 'max:190'],
            'duration_minutes' => ['required', 'integer', 'min:15', 'max:480'],
            'delivery_method' => ['required', Rule::enum(DeliveryMethod::class)],
            'fee' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['required', Rule::in(config('platform.currencies'))],
            'preparation_instructions' => ['nullable', 'string', 'max:2000'],
            'cancellation_policy' => ['nullable', 'string', 'max:2000'],
            'rescheduling_policy' => ['nullable', 'string', 'max:2000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_published' => ['boolean'],
        ];
    }

    protected function viewUrl(Model $record): ?string
    {
        return $record->is_published ? route('consultations.show', $record) : null;
    }
}

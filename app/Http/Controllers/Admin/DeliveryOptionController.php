<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Column;
use App\Admin\Field;
use App\Admin\ResourceController;
use App\Models\DeliveryOption;
use App\Support\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class DeliveryOptionController extends ResourceController
{
    protected string $model = DeliveryOption::class;

    protected string $routeName = 'admin.delivery-options';

    protected string $title = 'Delivery Options';

    protected string $singular = 'Delivery option';

    protected array $searchable = ['name'];

    protected string $orderBy = 'sort_order';

    protected string $orderDirection = 'asc';

    protected function description(): ?string
    {
        return 'Shown at checkout for orders containing printed books, filtered by the checkout currency.';
    }

    protected function columns(): array
    {
        return [Column::make('name', 'Name'), Column::make('currency', 'Currency'), Column::make('fee_display', 'Fee'), Column::make('estimate_label', 'Estimate'), Column::make('is_active', 'Active', 'boolean')];
    }

    protected function row(Model $record): array
    {
        return array_merge(parent::row($record), ['fee_display' => Money::format($record->fee, $record->currency), 'estimate_label' => $record->estimate() ?? '—']);
    }

    protected function fields(?Model $record): array
    {
        $currencies = array_map(fn ($c) => ['value' => $c, 'label' => $c], config('platform.currencies'));

        return [
            Field::text('name', 'Name')->required()->half()->placeholder('e.g. Lagos delivery'),
            Field::text('description', 'Description')->half(),
            Field::select('currency', 'Currency', $currencies)->required()->half()->set('default', 'NGN'),
            Field::money('fee', 'Fee')->required()->half(),
            Field::number('estimated_days_min', 'Estimated working days (from)')->half()->nullable(),
            Field::number('estimated_days_max', 'Estimated working days (to)')->half()->nullable(),
            Field::number('sort_order', 'Display order')->half(),
            Field::checkbox('is_active', 'Active')->set('default', true),
        ];
    }

    protected function rules(?Model $record): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:190'],
            'currency' => ['required', Rule::in(config('platform.currencies'))],
            'fee' => ['required', 'numeric', 'min:0'],
            'estimated_days_min' => ['nullable', 'integer', 'min:0', 'max:365'],
            'estimated_days_max' => ['nullable', 'integer', 'min:0', 'max:365', 'gte:estimated_days_min'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }
}

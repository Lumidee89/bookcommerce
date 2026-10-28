<?php

namespace App\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Support\Money;
use BackedEnum;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Shared CRUD for straightforward admin modules. Subclasses describe columns, fields and rules;
 * this class handles listing, validation, uploads, persistence and audit logging.
 */
abstract class ResourceController extends Controller
{
    /** @var class-string<Model> */
    protected string $model;

    protected string $routeName;

    protected string $title;

    protected string $singular;

    /** @var list<string> */
    protected array $searchable = ['title'];

    /** @var list<string> */
    protected array $with = [];

    protected string $orderBy = 'id';

    protected string $orderDirection = 'desc';

    protected bool $creatable = true;

    protected bool $deletable = true;

    /** @return list<array{key: string, label: string, type: string}> */
    abstract protected function columns(): array;

    /** @return list<Field> */
    abstract protected function fields(?Model $record): array;

    /** @return array<string, mixed> */
    abstract protected function rules(?Model $record): array;

    protected function description(): ?string
    {
        return null;
    }

    /** @return list<array{name: string, label: string, options: list<array>}> */
    protected function filters(): array
    {
        return [];
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        foreach ($this->filters() as $filter) {
            if (filled($filters[$filter['name']] ?? null)) {
                $query->where($filter['name'], $filters[$filter['name']]);
            }
        }
    }

    protected function row(Model $record): array
    {
        $row = [];
        foreach ($this->columns() as $column) {
            $value = $this->cellValue($record, $column['key']);
            $row[$column['key']] = match (true) {
                $value instanceof BackedEnum => Column::badge(method_exists($value, 'label') ? $value->label() : $value->value, Column::tone($value->value)),
                $value instanceof CarbonInterface => $column['type'] === 'date'
                    ? $value->format('j M Y')
                    : $value->copy()->setTimezone(config('platform.timezone'))->format('j M Y, H:i'),
                default => $value,
            };
        }

        return $row;
    }

    /** Reads a column value without ever treating a plain model method as a lazy-loaded relationship. */
    protected function cellValue(Model $record, string $key): mixed
    {
        $first = explode('.', $key)[0];
        if (method_exists($record, $first) && ! $record->relationLoaded($first) && ! array_key_exists($first, $record->getAttributes())) {
            return null;
        }

        return data_get($record, $key);
    }

    protected function viewUrl(Model $record): ?string
    {
        return null;
    }

    protected function showUrl(Model $record): ?string
    {
        return null;
    }

    protected function extra(?Model $record): ?array
    {
        return null;
    }

    public function index(Request $request): Response
    {
        $filters = $request->only(array_merge(['q'], array_column($this->filters(), 'name')));
        $query = $this->model::query()->with($this->with);

        if (filled($filters['q'] ?? null) && $this->searchable) {
            $term = $filters['q'];
            $query->where(fn (Builder $q) => collect($this->searchable)->each(fn ($col) => $q->orWhere($col, 'like', "%{$term}%")));
        }
        $this->applyFilters($query, $filters);

        $records = $this->scopeIndex($query)->orderBy($this->orderBy, $this->orderDirection)->paginate(20)->withQueryString();

        return Inertia::render('admin/resource/index', [
            'resource' => $this->meta(),
            'columns' => $this->columns(),
            'filters' => $this->filters(),
            'activeFilters' => $filters,
            'records' => $records->through(fn (Model $record) => [
                'id' => $record->getKey(),
                'cells' => $this->row($record),
                'edit_url' => $this->hasForm() ? route("{$this->routeName}.edit", $record->getKey()) : null,
                'show_url' => $this->showUrl($record),
                'view_url' => $this->viewUrl($record),
                'delete_url' => $this->deletable ? route("{$this->routeName}.destroy", $record->getKey()) : null,
            ]),
            'actions' => $this->indexActions(),
        ]);
    }

    protected function scopeIndex(Builder $query): Builder
    {
        return $query;
    }

    /** @return list<array{label: string, url: string, method?: string}> */
    protected function indexActions(): array
    {
        return [];
    }

    protected function hasForm(): bool
    {
        return true;
    }

    public function create(): Response
    {
        abort_unless($this->creatable, 404);

        return $this->form(null);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($this->creatable, 404);
        $data = $request->validate($this->rules(null));

        $record = DB::transaction(function () use ($data, $request) {
            $record = new $this->model;
            $this->save($record, $data, $request);

            return $record;
        });

        AuditLog::record('created', "Created {$this->singular} “{$this->labelFor($record)}”", $record);

        return redirect()->route("{$this->routeName}.edit", $record->getKey())->with('success', "{$this->singular} created.");
    }

    public function edit(string $record): Response
    {
        return $this->form($this->find($record));
    }

    public function update(Request $request, string $record): RedirectResponse
    {
        $model = $this->find($record);
        $data = $request->validate($this->rules($model));

        DB::transaction(fn () => $this->save($model, $data, $request));
        AuditLog::record('updated', "Updated {$this->singular} “{$this->labelFor($model)}”", $model, ['changed' => array_keys($model->getChanges())]);

        return back()->with('success', "{$this->singular} saved.");
    }

    public function destroy(string $record): RedirectResponse
    {
        abort_unless($this->deletable, 404);
        $model = $this->find($record);
        $label = $this->labelFor($model);
        $model->delete();
        AuditLog::record('deleted', "Deleted {$this->singular} “{$label}”", null, ['id' => $record, 'type' => class_basename($this->model)]);

        return redirect()->route("{$this->routeName}.index")->with('success', "{$this->singular} deleted.");
    }

    protected function find(string $id): Model
    {
        return $this->model::with($this->with)->findOrFail($id);
    }

    protected function labelFor(Model $record): string
    {
        return (string) ($record->title ?? $record->name ?? $record->reference ?? $record->email ?? "#{$record->getKey()}");
    }

    protected function form(?Model $record): Response
    {
        return Inertia::render('admin/resource/form', [
            'resource' => $this->meta(),
            'record' => $record ? ['id' => $record->getKey(), 'label' => $this->labelFor($record), 'view_url' => $this->viewUrl($record)] : null,
            'fields' => array_map(fn (Field $f) => $f->toArray(), $this->fields($record)),
            'values' => $this->values($record),
            'action' => $record ? route("{$this->routeName}.update", $record->getKey()) : route("{$this->routeName}.store"),
            'method' => $record ? 'put' : 'post',
            'extra' => $this->extra($record),
        ]);
    }

    protected function meta(): array
    {
        return [
            'title' => $this->title,
            'singular' => $this->singular,
            'description' => $this->description(),
            'index_url' => route("{$this->routeName}.index"),
            'create_url' => $this->creatable ? route("{$this->routeName}.create") : null,
        ];
    }

    protected function values(?Model $record): array
    {
        $values = [];
        foreach ($this->fields($record) as $field) {
            if ($field->type === 'heading') {
                continue;
            }
            $raw = $record ? $this->readValue($record, $field) : ($field->props['default'] ?? null);
            $values[$field->name] = $this->presentValue($raw, $field, $record);
        }

        return $values;
    }

    protected function readValue(Model $record, Field $field): mixed
    {
        return $record->getAttribute($field->name);
    }

    protected function presentValue(mixed $value, Field $field, ?Model $record): mixed
    {
        return match ($field->type) {
            'checkbox' => (bool) $value,
            'money' => $value === null ? '' : number_format($value / 100, 2, '.', ''),
            'date' => $value instanceof CarbonInterface ? $value->toDateString() : $value,
            'datetime' => $value instanceof CarbonInterface ? $value->copy()->setTimezone(config('platform.timezone'))->format('Y-m-d\TH:i') : $value,
            'time' => $value ? substr((string) $value, 0, 5) : $value,
            'tags' => is_array($value) ? implode(', ', $value) : $value,
            'image', 'file' => $value ? [
                'path' => $value,
                'url' => ($field->props['disk'] ?? 'public') === 'public' ? Storage::disk('public')->url($value) : null,
                'name' => basename($value),
            ] : null,
            'multiselect' => $value ?? [],
            default => $value instanceof BackedEnum ? $value->value : ($value ?? ''),
        };
    }

    /**
     * Copy validated input onto the model, handling uploads and type conversion.
     *
     * @param  array<string, mixed>  $data
     */
    protected function save(Model $record, array $data, Request $request): void
    {
        foreach ($this->fields($record->exists ? $record : null) as $field) {
            if (in_array($field->type, ['heading', 'multiselect'], true) || ($field->props['virtual'] ?? false)) {
                continue;
            }

            $name = $field->name;
            if (in_array($field->type, ['image', 'file'], true)) {
                $this->handleUpload($record, $field, $request);

                continue;
            }

            if ($field->type === 'checkbox') {
                $record->{$name} = (bool) ($data[$name] ?? false);

                continue;
            }

            if (! array_key_exists($name, $data)) {
                continue;
            }

            $record->{$name} = match ($field->type) {
                'money' => Money::toMinor($data[$name]),
                'number' => $data[$name] ?? (($field->props['nullable'] ?? false) ? null : 0),
                'datetime' => filled($data[$name]) ? Carbon::parse($data[$name], config('platform.timezone'))->utc() : null,
                'tags' => collect(explode(',', (string) $data[$name]))->map(fn ($t) => trim($t))->filter()->values()->all(),
                default => $data[$name] === '' ? null : $data[$name],
            };
        }

        $this->beforeSave($record, $data, $request);
        $record->save();
        $this->afterSave($record, $data, $request);
    }

    protected function beforeSave(Model $record, array $data, Request $request): void {}

    protected function afterSave(Model $record, array $data, Request $request): void {}

    protected function handleUpload(Model $record, Field $field, Request $request): void
    {
        $name = $field->name;
        $disk = $field->props['disk'] ?? 'public';
        $folder = $field->props['folder'] ?? Str::plural(Str::snake(class_basename($this->model)));

        if ($request->boolean("remove_{$name}") && $record->{$name}) {
            Storage::disk($disk)->delete($record->{$name});
            $record->{$name} = null;
        }

        if ($file = $request->file($name)) {
            if ($record->{$name}) {
                Storage::disk($disk)->delete($record->{$name});
            }
            $record->{$name} = $file->store($folder, $disk);
        }
    }

    /** Validation helper for upload fields. */
    protected static function imageRule(): array
    {
        return ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'];
    }

    protected static function documentRule(string $mimes = 'pdf', int $maxKb = 20480): array
    {
        return ['nullable', 'file', "mimes:{$mimes}", "max:{$maxKb}"];
    }
}

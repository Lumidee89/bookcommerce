<?php

namespace App\Http\Controllers\Admin;

use App\Admin\ResourceController;
use App\Models\AuditLog;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Shared list/detail/status workflow for contact enquiries and speaking invitations.
 */
abstract class InboxController extends ResourceController
{
    protected bool $creatable = false;

    /** @var class-string<\BackedEnum> */
    protected string $statusEnum;

    /** @return array<string, string> field => label, shown on the detail page */
    abstract protected function detailFields(): array;

    abstract protected function heading(Model $record): string;

    protected function hasForm(): bool
    {
        return false;
    }

    protected function fields(?Model $record): array
    {
        return [];
    }

    protected function rules(?Model $record): array
    {
        return [];
    }

    protected function filters(): array
    {
        return [['name' => 'status', 'label' => 'Status', 'options' => $this->statusEnum::options()]];
    }

    protected function showUrl(Model $record): ?string
    {
        return route("{$this->routeName}.show", $record->getKey());
    }

    public function show(string $record): Response
    {
        $model = $this->model::with('notes.user')->findOrFail($record);

        $details = [];
        foreach ($this->detailFields() as $field => $label) {
            $value = $model->{$field};
            $details[] = ['label' => $label, 'value' => match (true) {
                $value instanceof \BackedEnum => method_exists($value, 'label') ? $value->label() : $value->value,
                $value instanceof CarbonInterface => $value->format('j F Y'),
                default => $value,
            }];
        }

        return Inertia::render('admin/inbox/show', [
            'resource' => $this->meta(),
            'item' => [
                'id' => $model->getKey(),
                'reference' => $model->reference,
                'heading' => $this->heading($model),
                'email' => $model->email,
                'status' => $model->status->value,
                'received_at' => $model->created_at->toIso8601String(),
                'details' => $details,
                'notes' => $model->notes->map(fn ($n) => ['id' => $n->id, 'body' => $n->body, 'user' => $n->user?->name, 'at' => $n->created_at->toIso8601String()]),
            ],
            'statusOptions' => $this->statusEnum::options(),
            'statusUrl' => route("{$this->routeName}.status", $model->getKey()),
            'notesUrl' => route('admin.notes.store'),
            'notableType' => $model->getMorphClass(),
        ]);
    }

    public function updateStatus(Request $request, string $record): RedirectResponse
    {
        $model = $this->model::findOrFail($record);
        $data = $request->validate(['status' => ['required', Rule::in($this->statusEnum::values())]]);
        $model->update(['status' => $data['status']]);
        AuditLog::record('status.changed', "{$this->singular} {$model->reference} set to {$model->status->label()}", $model);

        return back()->with('success', 'Status updated.');
    }
}

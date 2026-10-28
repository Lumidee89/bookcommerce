<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Column;
use App\Admin\ResourceController;
use App\Enums\SubscriberStatus;
use App\Models\AuditLog;
use App\Models\NewsletterSubscriber;
use App\Services\Newsletter\NewsletterProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubscriberController extends ResourceController
{
    protected string $model = NewsletterSubscriber::class;

    protected string $routeName = 'admin.subscribers';

    protected string $title = 'Subscribers';

    protected string $singular = 'Subscriber';

    protected array $searchable = ['email', 'name'];

    protected array $with = ['interests'];

    protected bool $creatable = false;

    protected function description(): ?string
    {
        $provider = app(NewsletterProvider::class);

        return $provider->isConfigured()
            ? "Confirmed subscribers are synchronised to {$provider->name()}."
            : 'No email marketing provider is configured yet — subscribers are stored here only (pending integration).';
    }

    protected function filters(): array
    {
        return [['name' => 'status', 'label' => 'Status', 'options' => SubscriberStatus::options()]];
    }

    protected function columns(): array
    {
        return [Column::make('email', 'Email'), Column::make('name', 'Name'), Column::make('status', 'Status'), Column::make('interest_names', 'Interests'), Column::make('confirmed_at', 'Confirmed')];
    }

    protected function row(Model $record): array
    {
        return array_merge(parent::row($record), ['interest_names' => $record->interests->pluck('name')->implode(', ') ?: '—']);
    }

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

    protected function indexActions(): array
    {
        return [['label' => 'Export CSV', 'url' => route('admin.subscribers.export'), 'download' => true]];
    }

    public function export(Request $request): StreamedResponse
    {
        AuditLog::record('subscribers.exported', 'Exported subscriber list');

        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['email', 'name', 'status', 'interests', 'confirmed_at', 'unsubscribed_at', 'source']);
            NewsletterSubscriber::with('interests')->orderBy('id')->chunk(500, function ($chunk) use ($out) {
                foreach ($chunk as $s) {
                    fputcsv($out, [$s->email, $s->name, $s->status->value, $s->interests->pluck('name')->implode('; '), $s->confirmed_at?->toIso8601String(), $s->unsubscribed_at?->toIso8601String(), $s->source]);
                }
            });
            fclose($out);
        }, 'subscribers-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }
}

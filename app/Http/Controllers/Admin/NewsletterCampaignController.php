<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Column;
use App\Admin\Field;
use App\Admin\ResourceController;
use App\Jobs\SendNewsletterCampaign;
use App\Mail\NewsletterMail;
use App\Models\AuditLog;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterInterest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Throwable;

class NewsletterCampaignController extends ResourceController
{
    protected string $model = NewsletterCampaign::class;

    protected string $routeName = 'admin.newsletters';

    protected string $title = 'Newsletters';

    protected string $singular = 'Newsletter';

    protected array $searchable = ['subject'];

    protected function description(): ?string
    {
        return 'Write a newsletter, send yourself a test, then send it to confirmed subscribers. Every email includes an unsubscribe link.';
    }

    protected function columns(): array
    {
        return [Column::make('subject', 'Subject'), Column::make('status_badge', 'Status'), Column::make('progress', 'Sent'), Column::make('sent_at', 'Sent on')];
    }

    protected function row(Model $record): array
    {
        return array_merge(parent::row($record), [
            'status_badge' => Column::badge(ucfirst($record->status), ['draft' => 'neutral', 'sending' => 'warning', 'sent' => 'success'][$record->status] ?? 'neutral'),
            'progress' => $record->status === 'draft' ? '—' : "{$record->sent_count} / {$record->recipients_count}".($record->failed_count ? " ({$record->failed_count} failed)" : ''),
        ]);
    }

    protected function fields(?Model $record): array
    {
        $locked = $record && ! $record->isEditable();

        return [
            Field::text('subject', 'Subject line')->required()->set('disabled', $locked),
            Field::text('preheader', 'Preview text')->help('Short line shown next to the subject in most inboxes.')->set('disabled', $locked),
            Field::markdown('body', 'Message')->required()->rows(18)->set('disabled', $locked)
                ->help('Markdown supported: ## heading, **bold**, [link text](https://…), - bullet. The greeting and unsubscribe link are added automatically.'),
            Field::multiselect('interest_ids', 'Send to interest groups', NewsletterInterest::orderBy('sort_order')->get()->map(fn ($i) => ['value' => $i->id, 'label' => $i->name])->all())
                ->help('Leave all unticked to send to every confirmed subscriber.')->set('disabled', $locked),
        ];
    }

    protected function rules(?Model $record): array
    {
        return [
            'subject' => ['required', 'string', 'max:190'],
            'preheader' => ['nullable', 'string', 'max:190'],
            'body' => ['required', 'string', 'max:100000'],
            'interest_ids' => ['nullable', 'array'],
            'interest_ids.*' => ['integer', 'exists:newsletter_interests,id'],
        ];
    }

    protected function readValue(Model $record, Field $field): mixed
    {
        return $field->name === 'interest_ids' ? ($record->interest_ids ?? []) : parent::readValue($record, $field);
    }

    protected function beforeSave(Model $record, array $data, Request $request): void
    {
        abort_if($record->exists && ! $record->isEditable(), 422, 'A newsletter cannot be changed after it has been sent.');
        $record->interest_ids = array_map('intval', $data['interest_ids'] ?? []) ?: null;
        $record->user_id ??= $request->user()->id;
        $record->status ??= 'draft';
    }

    protected function extra(?Model $record): ?array
    {
        if (! $record) {
            return null;
        }

        return [
            'type' => 'newsletter-send',
            'status' => $record->status,
            'audience' => $record->audience()->count(),
            'recipients' => $record->recipients_count,
            'sent' => $record->sent_count,
            'failed' => $record->failed_count,
            'sent_at' => $record->sent_at?->toIso8601String(),
            'test_url' => route('admin.newsletters.test', $record->id),
            'send_url' => route('admin.newsletters.send', $record->id),
            'test_email' => auth()->user()->email,
        ];
    }

    public function sendTest(Request $request, string $record): RedirectResponse
    {
        $campaign = NewsletterCampaign::findOrFail($record);
        $data = $request->validate(['email' => ['required', 'email']]);

        try {
            Mail::to($data['email'])->send(new NewsletterMail($campaign));
        } catch (Throwable $e) {
            return back()->with('error', 'The test email could not be sent: '.$e->getMessage());
        }

        return back()->with('success', "Test sent to {$data['email']}.");
    }

    public function send(string $record): RedirectResponse
    {
        $campaign = NewsletterCampaign::findOrFail($record);
        abort_unless($campaign->isEditable(), 422, 'This newsletter has already been sent.');

        $audience = $campaign->audience()->count();
        if ($audience === 0) {
            return back()->with('error', 'There are no confirmed subscribers in the selected groups.');
        }

        $campaign->update(['status' => 'sending', 'recipients_count' => $audience]);
        SendNewsletterCampaign::dispatch($campaign);
        AuditLog::record('newsletter.sent', "Sent newsletter “{$campaign->subject}” to {$audience} subscriber(s)", $campaign);

        return back()->with('success', "Sending to {$audience} subscriber(s). Progress updates on this page.");
    }
}

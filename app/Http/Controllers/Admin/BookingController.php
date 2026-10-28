<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Column;
use App\Admin\ResourceController;
use App\Enums\BookingStatus;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Notifications\BookingConfirmed;
use App\Services\Bookings\BookingPolicyException;
use App\Services\Bookings\BookingService;
use App\Services\Bookings\SlotUnavailableException;
use App\Support\Money;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends ResourceController
{
    protected string $model = Booking::class;

    protected string $routeName = 'admin.bookings';

    protected string $title = 'Consultation Bookings';

    protected string $singular = 'Booking';

    protected array $searchable = ['reference', 'name', 'email', 'service_name'];

    protected string $orderBy = 'starts_at';

    protected bool $creatable = false;

    protected bool $deletable = false;

    protected function filters(): array
    {
        return [['name' => 'status', 'label' => 'Status', 'options' => BookingStatus::options()]];
    }

    protected function columns(): array
    {
        return [Column::make('reference', 'Reference'), Column::make('starts_at', 'When ('.config('platform.timezone').')'), Column::make('name', 'Client'), Column::make('service_name', 'Service'), Column::make('status', 'Status')];
    }

    protected function hasForm(): bool
    {
        return false;
    }

    protected function showUrl(Model $record): ?string
    {
        return route('admin.bookings.show', $record);
    }

    protected function fields(?Model $record): array
    {
        return [];
    }

    protected function rules(?Model $record): array
    {
        return [];
    }

    public function show(Booking $booking): Response
    {
        $booking->load(['service', 'transactions', 'statusHistory.user', 'notes.user']);

        return Inertia::render('admin/bookings/show', [
            'booking' => [
                ...$booking->toPublicArray(),
                'id' => $booking->id,
                'phone' => $booking->phone,
                'organisation' => $booking->organisation,
                'needs' => $booking->needs,
                'meeting_url' => $booking->meeting_url,
                'meeting_details' => $booking->meeting_details,
                'cancellation_reason' => $booking->cancellation_reason,
                'reschedule_count' => $booking->reschedule_count,
                'fee_display' => $booking->fee > 0 ? Money::format($booking->fee, $booking->currency) : 'Free',
                'transactions' => $booking->transactions->map(fn ($t) => ['reference' => $t->reference, 'amount' => $t->amount, 'currency' => $t->currency, 'status_label' => $t->status->label(), 'status' => $t->status->value, 'created_at' => $t->created_at->toIso8601String()]),
                'history' => $booking->statusHistory->map(fn ($h) => ['to' => BookingStatus::tryFrom($h->to_status)?->label() ?? $h->to_status, 'note' => $h->note, 'user' => $h->user?->name, 'at' => $h->created_at?->toIso8601String()]),
                'notes' => $booking->notes->map(fn ($n) => ['id' => $n->id, 'body' => $n->body, 'user' => $n->user?->name, 'at' => $n->created_at->toIso8601String()]),
            ],
            'service' => $booking->service ? ['slug' => $booking->service->slug, 'duration_minutes' => $booking->service->duration_minutes] : null,
            'ownerTimezone' => config('platform.timezone'),
        ]);
    }

    public function updateMeeting(Request $request, Booking $booking): RedirectResponse
    {
        $data = $request->validate([
            'meeting_url' => ['nullable', 'url', 'max:500'],
            'meeting_details' => ['nullable', 'string', 'max:2000'],
            'notify' => ['boolean'],
        ]);

        $booking->update(['meeting_url' => $data['meeting_url'] ?? null, 'meeting_details' => $data['meeting_details'] ?? null]);
        AuditLog::record('booking.meeting', "Meeting details updated for {$booking->reference}", $booking);

        if (($data['notify'] ?? false) && $booking->status === BookingStatus::Confirmed) {
            Notification::route('mail', [$booking->email => $booking->name])->notify(new BookingConfirmed($booking));
        }

        return back()->with('success', 'Meeting details saved.');
    }

    public function updateStatus(Request $request, Booking $booking, BookingService $bookings): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['completed', 'no_show', 'cancelled'])],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            if ($data['status'] === 'cancelled') {
                $bookings->cancel($booking, $data['reason'] ?? null, byClient: false, userId: $request->user()->id);
            } else {
                abort_unless($booking->status === BookingStatus::Confirmed, 422, 'Only confirmed bookings can be marked completed or no-show.');
                $booking->transitionTo(BookingStatus::from($data['status']), $data['reason'] ?? null, $request->user()->id);
            }
        } catch (BookingPolicyException $e) {
            return back()->with('error', $e->getMessage());
        }

        AuditLog::record('booking.status', "Booking {$booking->reference} set to {$booking->status->label()}", $booking);

        return back()->with('success', 'Booking updated.');
    }

    public function reschedule(Request $request, Booking $booking, BookingService $bookings): RedirectResponse
    {
        $data = $request->validate(['starts_at' => ['required', 'date']]);

        try {
            $bookings->reschedule($booking, CarbonImmutable::parse($data['starts_at'])->utc(), byClient: false, userId: $request->user()->id);
        } catch (BookingPolicyException|SlotUnavailableException $e) {
            return back()->with('error', $e->getMessage());
        }

        AuditLog::record('booking.rescheduled', "Booking {$booking->reference} rescheduled", $booking);

        return back()->with('success', 'Booking rescheduled and the client notified.');
    }
}

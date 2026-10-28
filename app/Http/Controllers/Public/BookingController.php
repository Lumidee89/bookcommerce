<?php

namespace App\Http\Controllers\Public;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\Bookings\BookingPolicyException;
use App\Services\Bookings\BookingService;
use App\Services\Bookings\SlotUnavailableException;
use App\Services\Payments\PaymentException;
use App\Services\Payments\PaymentService;
use App\Support\Seo;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * Client self-service for an existing booking, reached via the signed link in their emails.
 */
class BookingController extends Controller
{
    public function __construct(private readonly BookingService $bookings) {}

    public function manage(Request $request, Booking $booking): Response
    {
        $this->authorizeToken($request, $booking);
        $booking->load('service');

        $canChange = $booking->status === BookingStatus::Confirmed && $this->bookings->clientMayChange($booking);

        return Inertia::render('public/consultations/manage', [
            'booking' => $booking->toPublicArray(),
            'token' => $booking->manage_token,
            'canCancel' => $canChange || $booking->status === BookingStatus::PendingPayment,
            'canReschedule' => $canChange,
            'canPay' => $booking->status === BookingStatus::PendingPayment && $booking->hold_expires_at?->isFuture(),
            'policy' => [
                'cancellation' => $booking->service?->cancellation_policy,
                'rescheduling' => $booking->service?->rescheduling_policy,
            ],
            'seo' => Seo::make("Booking {$booking->reference}"),
        ]);
    }

    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorizeToken($request, $booking);
        $data = $request->validate(['reason' => ['nullable', 'string', 'max:500']]);

        try {
            $this->bookings->cancel($booking, $data['reason'] ?? null, byClient: true);
        } catch (BookingPolicyException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Your booking has been cancelled.');
    }

    public function reschedule(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorizeToken($request, $booking);
        $data = $request->validate(['starts_at' => ['required', 'date'], 'timezone' => ['required', 'timezone:all']]);

        try {
            $booking->client_timezone = $data['timezone'];
            $this->bookings->reschedule($booking, CarbonImmutable::parse($data['starts_at'])->utc(), byClient: true);
        } catch (BookingPolicyException $e) {
            return back()->with('error', $e->getMessage());
        } catch (SlotUnavailableException $e) {
            return back()->withErrors(['starts_at' => $e->getMessage()]);
        }

        return back()->with('success', 'Your booking has been rescheduled. A new confirmation has been emailed to you.');
    }

    public function pay(Request $request, Booking $booking, PaymentService $payments): HttpResponse
    {
        $this->authorizeToken($request, $booking);
        if ($booking->status !== BookingStatus::PendingPayment || ! $booking->hold_expires_at?->isFuture() || ! $payments->available()) {
            return back()->with('error', 'This booking can no longer be paid for. Please book a new time.');
        }

        try {
            $transaction = $payments->createTransaction($booking, $booking->fee, $booking->currency);

            return Inertia::location($payments->checkoutUrl($transaction, $booking->email));
        } catch (PaymentException) {
            return back()->with('error', 'We could not start the payment. Please try again.');
        }
    }

    private function authorizeToken(Request $request, Booking $booking): void
    {
        abort_unless(hash_equals($booking->manage_token, (string) ($request->query('token') ?? $request->input('token'))), 403);
    }
}

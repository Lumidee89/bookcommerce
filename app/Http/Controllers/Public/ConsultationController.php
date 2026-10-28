<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookingRequest;
use App\Models\ConsultationService;
use App\Models\Page;
use App\Services\Bookings\AvailabilityService;
use App\Services\Bookings\BookingService;
use App\Services\Bookings\SlotUnavailableException;
use App\Services\Payments\PaymentException;
use App\Services\Payments\PaymentService;
use App\Support\Seo;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class ConsultationController extends Controller
{
    public function __construct(private readonly AvailabilityService $availability) {}

    public function index(): Response
    {
        return Inertia::render('public/consultations/index', [
            'services' => ConsultationService::published()->get()->map->toCardArray(),
            'seo' => Seo::make('Consultations', 'Book a consultation with Yemi Adebanjo on leadership, business direction, ministry structure, succession and publishing.'),
        ]);
    }

    public function show(ConsultationService $service, PaymentService $payments): Response
    {
        abort_unless($service->is_published, 404);

        return Inertia::render('public/consultations/show', [
            'service' => $service->toDetailArray(),
            'ownerTimezone' => $this->availability->settings()->timezone,
            'bookable' => ! $service->requiresPayment() || $payments->available(),
            'policyUrl' => Page::where('slug', 'booking-policy')->where('is_published', true)->exists() ? route('pages.show', 'booking-policy') : null,
            'seo' => Seo::make($service->name, $service->summary),
        ]);
    }

    public function dates(Request $request, ConsultationService $service): JsonResponse
    {
        abort_unless($service->is_published, 404);
        $data = $request->validate(['month' => ['required', 'date_format:Y-m']]);

        return response()->json(['dates' => $this->availability->availableDatesInMonth($service, $data['month'])]);
    }

    public function slots(Request $request, ConsultationService $service): JsonResponse
    {
        abort_unless($service->is_published, 404);
        $data = $request->validate(['date' => ['required', 'date_format:Y-m-d']]);

        return response()->json([
            'slots' => array_map(fn (CarbonImmutable $s) => $s->toIso8601String(), $this->availability->slotsForDate($service, $data['date'])),
        ]);
    }

    public function book(BookingRequest $request, ConsultationService $service, BookingService $bookings, PaymentService $payments): HttpResponse
    {
        abort_unless($service->is_published, 404);

        if ($service->requiresPayment() && ! $payments->available()) {
            return back()->with('error', 'Online payment for this service is not available yet. Please send an enquiry instead.');
        }

        try {
            $booking = $bookings->create($service, CarbonImmutable::parse($request->validated('starts_at'))->utc(), $request->validated());
        } catch (SlotUnavailableException $e) {
            return back()->withErrors(['starts_at' => $e->getMessage()]);
        }

        if (! $service->requiresPayment()) {
            return redirect($booking->manageUrl())->with('success', 'Your consultation is confirmed. A confirmation email is on its way.');
        }

        try {
            $transaction = $payments->createTransaction($booking, $booking->fee, $booking->currency);

            return Inertia::location($payments->checkoutUrl($transaction, $booking->email));
        } catch (PaymentException) {
            return redirect($booking->manageUrl())->with('error', 'We could not start the payment. Please try again before your reservation expires.');
        }
    }
}

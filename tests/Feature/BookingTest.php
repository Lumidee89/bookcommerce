<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Models\AvailabilityException;
use App\Models\AvailabilityRule;
use App\Models\Booking;
use App\Models\ConsultationService;
use App\Models\Transaction;
use App\Services\Bookings\AvailabilityService;
use App\Services\Bookings\BookingService;
use App\Services\Bookings\SlotUnavailableException;
use App\Services\Payments\TestGateway;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    private ConsultationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        // Monday 2 Nov 2026, 08:00 Lagos.
        $this->travelTo(CarbonImmutable::parse('2026-11-02 08:00', 'Africa/Lagos'));

        foreach (range(1, 5) as $day) {
            AvailabilityRule::create(['day_of_week' => $day, 'start_time' => '10:00', 'end_time' => '13:00']);
        }
        $this->service = ConsultationService::create(['name' => 'Leadership', 'duration_minutes' => 60, 'fee' => 0, 'currency' => 'NGN', 'delivery_method' => 'online', 'is_published' => true]);
    }

    private function slots(string $date): array
    {
        return array_map(fn ($s) => $s->setTimezone('Africa/Lagos')->format('H:i'), app(AvailabilityService::class)->slotsForDate($this->service, $date));
    }

    private function client(): array
    {
        return ['name' => 'Client', 'email' => 'c@example.com', 'timezone' => 'Europe/London', 'needs' => 'Advice'];
    }

    public function test_slots_follow_working_hours_notice_and_exceptions()
    {
        // Wednesday: 10:00–13:00 with 60-min sessions every 30 minutes.
        $this->assertSame(['10:00', '10:30', '11:00', '11:30', '12:00'], $this->slots('2026-11-04'));
        // Within the 24h minimum notice.
        $this->assertSame([], $this->slots('2026-11-02'));
        // Weekend.
        $this->assertSame([], $this->slots('2026-11-07'));

        AvailabilityException::create(['date' => '2026-11-05']);
        $this->assertSame([], $this->slots('2026-11-05'));
    }

    public function test_bookings_remove_overlapping_slots_including_buffer()
    {
        app(BookingService::class)->create($this->service, CarbonImmutable::parse('2026-11-04 10:00', 'Africa/Lagos')->utc(), $this->client());

        // 10:00–11:00 is taken; with a 15-minute buffer the next start is 11:30.
        $this->assertSame(['11:30', '12:00'], $this->slots('2026-11-04'));
    }

    public function test_the_same_slot_cannot_be_booked_twice()
    {
        $start = CarbonImmutable::parse('2026-11-04 10:00', 'Africa/Lagos')->utc();
        app(BookingService::class)->create($this->service, $start, $this->client());

        $this->expectException(SlotUnavailableException::class);
        app(BookingService::class)->create($this->service, $start, $this->client());
    }

    public function test_free_booking_is_confirmed_through_the_public_form()
    {
        $this->post("/consultations/{$this->service->slug}/book", $this->client() + [
            'starts_at' => CarbonImmutable::parse('2026-11-04 10:00', 'Africa/Lagos')->toIso8601String(),
            'accept_policy' => true,
        ])->assertRedirect();

        $this->assertSame(BookingStatus::Confirmed, Booking::sole()->status);

        $this->post("/consultations/{$this->service->slug}/book", $this->client() + [
            'starts_at' => CarbonImmutable::parse('2026-11-04 10:00', 'Africa/Lagos')->toIso8601String(),
            'accept_policy' => true,
        ])->assertSessionHasErrors('starts_at');
    }

    public function test_paid_booking_holds_slot_until_payment_and_releases_expired_holds()
    {
        $this->service->update(['fee' => 5000000]);
        $start = CarbonImmutable::parse('2026-11-04 10:00', 'Africa/Lagos')->utc();

        $this->post("/consultations/{$this->service->slug}/book", $this->client() + ['starts_at' => $start->toIso8601String(), 'accept_policy' => true]);
        $booking = Booking::sole();
        $this->assertSame(BookingStatus::PendingPayment, $booking->status);
        $this->assertNotContains('10:00', $this->slots('2026-11-04'));

        $this->travel(config('platform.booking.hold_minutes') + 1)->minutes();
        app(BookingService::class)->expireHolds();
        $this->assertSame(BookingStatus::Expired, $booking->fresh()->status);
        $this->assertContains('10:00', $this->slots('2026-11-04'));
    }

    public function test_verified_payment_confirms_held_booking()
    {
        $this->service->update(['fee' => 5000000]);
        $start = CarbonImmutable::parse('2026-11-04 10:00', 'Africa/Lagos')->utc();
        $this->post("/consultations/{$this->service->slug}/book", $this->client() + ['starts_at' => $start->toIso8601String(), 'accept_policy' => true]);

        $transaction = Transaction::sole();
        app(TestGateway::class)->simulate($transaction, 'success');
        $this->get('/payments/callback?reference='.$transaction->reference)->assertRedirect();

        $this->assertSame(BookingStatus::Confirmed, Booking::sole()->status);
    }

    public function test_manage_page_requires_signed_token()
    {
        $booking = app(BookingService::class)->create($this->service, CarbonImmutable::parse('2026-11-04 10:00', 'Africa/Lagos')->utc(), $this->client());

        $this->get("/bookings/{$booking->reference}")->assertForbidden();
        $this->get($booking->manageUrl())->assertOk();
        $this->post("/bookings/{$booking->reference}/cancel", ['token' => 'wrong'])->assertForbidden();
        $this->post("/bookings/{$booking->reference}/cancel", ['token' => $booking->manage_token])->assertRedirect();
        $this->assertSame(BookingStatus::Cancelled, $booking->fresh()->status);
    }
}

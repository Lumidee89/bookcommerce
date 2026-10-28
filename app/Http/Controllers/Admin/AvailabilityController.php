<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\AvailabilityException;
use App\Models\AvailabilityRule;
use App\Models\Setting;
use App\Services\Bookings\BookingSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AvailabilityController extends Controller
{
    public function edit(): Response
    {
        $settings = BookingSettings::current();

        return Inertia::render('admin/availability', [
            'timezone' => $settings->timezone,
            'rules' => AvailabilityRule::orderBy('day_of_week')->orderBy('start_time')->get()->map(fn ($r) => [
                'day_of_week' => $r->day_of_week, 'start_time' => substr($r->start_time, 0, 5), 'end_time' => substr($r->end_time, 0, 5),
            ]),
            'exceptions' => AvailabilityException::where('date', '>=', now()->subDay()->toDateString())->orderBy('date')->get()->map(fn ($e) => [
                'id' => $e->id, 'date' => $e->date->toDateString(), 'start_time' => $e->start_time ? substr($e->start_time, 0, 5) : null,
                'end_time' => $e->end_time ? substr($e->end_time, 0, 5) : null, 'reason' => $e->reason,
                'delete_url' => route('admin.availability.exceptions.destroy', $e),
            ]),
            'settings' => [
                'slot_interval_minutes' => $settings->slotInterval,
                'buffer_minutes' => $settings->bufferMinutes,
                'min_notice_hours' => $settings->minNoticeHours,
                'max_advance_days' => $settings->maxAdvanceDays,
                'max_per_day' => $settings->maxPerDay,
                'change_notice_hours' => $settings->changeNoticeHours,
                'max_reschedules' => $settings->maxReschedules,
            ],
        ]);
    }

    public function updateRules(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'rules' => ['array'],
            'rules.*.day_of_week' => ['required', 'integer', 'between:0,6'],
            'rules.*.start_time' => ['required', 'date_format:H:i'],
            'rules.*.end_time' => ['required', 'date_format:H:i', 'after:rules.*.start_time'],
            'settings' => ['required', 'array'],
            'settings.slot_interval_minutes' => ['required', 'integer', 'between:5,240'],
            'settings.buffer_minutes' => ['required', 'integer', 'between:0,240'],
            'settings.min_notice_hours' => ['required', 'integer', 'between:0,720'],
            'settings.max_advance_days' => ['required', 'integer', 'between:1,365'],
            'settings.max_per_day' => ['required', 'integer', 'between:0,50'],
            'settings.change_notice_hours' => ['required', 'integer', 'between:0,720'],
            'settings.max_reschedules' => ['required', 'integer', 'between:0,10'],
        ]);

        DB::transaction(function () use ($data) {
            AvailabilityRule::query()->delete();
            foreach ($data['rules'] ?? [] as $rule) {
                AvailabilityRule::create($rule + ['is_active' => true]);
            }
        });

        Setting::put(collect($data['settings'])->mapWithKeys(fn ($v, $k) => ["booking.{$k}" => (int) $v])->all());
        AuditLog::record('availability.updated', 'Updated working hours and booking rules');

        return back()->with('success', 'Availability saved.');
    }

    public function storeException(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['nullable', 'date_format:H:i', 'required_with:end_time'],
            'end_time' => ['nullable', 'date_format:H:i', 'required_with:start_time', 'after:start_time'],
            'reason' => ['nullable', 'string', 'max:190'],
        ]);

        AvailabilityException::create($data);

        return back()->with('success', 'Date added.');
    }

    public function destroyException(AvailabilityException $exception): RedirectResponse
    {
        $exception->delete();

        return back()->with('success', 'Date removed.');
    }

    public static function defaults(): array
    {
        return BookingSettings::defaults();
    }
}

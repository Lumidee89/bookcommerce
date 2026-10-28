<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ContactEnquiry;
use App\Models\Order;
use App\Models\SpeakingInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NoteController extends Controller
{
    /** Notable types and the permission needed to annotate them. */
    private const TYPES = [
        'order' => [Order::class, 'manage orders'],
        'booking' => [Booking::class, 'manage bookings'],
        'enquiry' => [ContactEnquiry::class, 'manage enquiries'],
        'invitation' => [SpeakingInvitation::class, 'manage enquiries'],
    ];

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'notable_type' => ['required', Rule::in(array_keys(self::TYPES))],
            'notable_id' => ['required', 'integer'],
            'body' => ['required', 'string', 'max:3000'],
        ]);

        [$class, $permission] = self::TYPES[$data['notable_type']];
        abort_unless($request->user()->can($permission), 403);

        $class::findOrFail($data['notable_id'])->notes()->create(['user_id' => $request->user()->id, 'body' => $data['body']]);

        return back()->with('success', 'Note added.');
    }
}

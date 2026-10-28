<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Enums\EnquiryStatus;
use App\Enums\InvitationStatus;
use App\Enums\SubscriberStatus;
use App\Enums\TransactionStatus;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\ContactEnquiry;
use App\Models\NewsletterSubscriber;
use App\Models\Order;
use App\Models\Refund;
use App\Models\SpeakingInvitation;
use App\Models\Transaction;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Every figure here comes from database records for a stated date range (BR-017).
 */
class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $range = $request->validate(['range' => ['nullable', 'in:7,30,90,365']])['range'] ?? '30';
        $from = CarbonImmutable::now()->subDays((int) $range)->startOfDay();
        $user = $request->user();

        $data = ['range' => (int) $range, 'from' => $from->toIso8601String(), 'to' => now()->toIso8601String()];

        if ($user->can('manage orders') || $user->can('view reports')) {
            $successful = Transaction::where('status', TransactionStatus::Success)->where('verified_at', '>=', $from);
            $data['sales'] = [
                'revenue' => (clone $successful)->where('payable_type', 'order')->selectRaw('currency, SUM(amount) as total, COUNT(*) as count')->groupBy('currency')->get(),
                'consultation_revenue' => (clone $successful)->where('payable_type', 'booking')->selectRaw('currency, SUM(amount) as total, COUNT(*) as count')->groupBy('currency')->get(),
                'refunds' => Refund::where('created_at', '>=', $from)->selectRaw('currency, SUM(amount) as total, COUNT(*) as count')->groupBy('currency')->get(),
                'pending_transactions' => Transaction::where('status', TransactionStatus::Pending)->where('created_at', '>=', $from)->count(),
                'failed_transactions' => Transaction::whereIn('status', [TransactionStatus::Failed, TransactionStatus::Abandoned])->where('created_at', '>=', $from)->count(),
                'awaiting_fulfilment' => Order::whereIn('status', ['paid', 'processing', 'ready_for_dispatch'])->where('requires_shipping', true)->count(),
                'recent_orders' => Order::latest()->take(6)->get()->map(fn ($o) => [
                    'reference' => $o->reference, 'customer' => $o->customer_name, 'total' => $o->total, 'currency' => $o->currency,
                    'status' => $o->status->value, 'status_label' => $o->status->label(), 'url' => route('admin.orders.show', $o), 'at' => $o->created_at->toIso8601String(),
                ]),
            ];
        }

        if ($user->can('manage bookings')) {
            $data['bookings'] = [
                'upcoming' => Booking::where('status', BookingStatus::Confirmed)->where('starts_at', '>=', now())->count(),
                'pending_payment' => Booking::where('status', BookingStatus::PendingPayment)->where('hold_expires_at', '>', now())->count(),
                'attention' => Booking::where('status', BookingStatus::RequiresAttention)->count(),
                'next' => Booking::where('status', BookingStatus::Confirmed)->where('starts_at', '>=', now())->orderBy('starts_at')->take(5)->get()->map(fn ($b) => [
                    'reference' => $b->reference, 'name' => $b->name, 'service' => $b->service_name, 'starts_at' => $b->starts_at->toIso8601String(), 'url' => route('admin.bookings.show', $b),
                ]),
            ];
        }

        if ($user->can('manage enquiries')) {
            $data['inbox'] = [
                'new_invitations' => SpeakingInvitation::where('status', InvitationStatus::New)->count(),
                'unresolved_enquiries' => ContactEnquiry::whereNotIn('status', [EnquiryStatus::Resolved, EnquiryStatus::Closed])->count(),
            ];
        }

        if ($user->can('manage subscribers')) {
            $data['subscribers'] = [
                'active' => NewsletterSubscriber::where('status', SubscriberStatus::Subscribed)->count(),
                'new_in_range' => NewsletterSubscriber::where('status', SubscriberStatus::Subscribed)->where('confirmed_at', '>=', $from)->count(),
            ];
        }

        if ($user->can('view audit logs')) {
            $data['activity'] = AuditLog::with('user')->latest('created_at')->take(8)->get()->map(fn ($a) => [
                'description' => $a->description, 'user' => $a->user?->name ?? 'System', 'at' => $a->created_at->toIso8601String(),
            ]);
        }

        return Inertia::render('admin/dashboard', $data);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Column;
use App\Admin\ResourceController;
use App\Enums\OrderStatus;
use App\Enums\TransactionStatus;
use App\Models\AuditLog;
use App\Models\DigitalDownload;
use App\Models\Order;
use App\Notifications\OrderPaid;
use App\Notifications\OrderStatusUpdated;
use App\Services\Shop\OrderFulfilmentService;
use App\Support\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends ResourceController
{
    protected string $model = Order::class;

    protected string $routeName = 'admin.orders';

    protected string $title = 'Orders';

    protected string $singular = 'Order';

    protected array $searchable = ['reference', 'customer_name', 'email'];

    protected bool $creatable = false;

    protected bool $deletable = false;

    /** Statuses staff may set by hand. Paid states only ever come from verified payments. */
    private const FULFILMENT_STATUSES = ['processing', 'ready_for_dispatch', 'shipped', 'delivered', 'completed'];

    protected function filters(): array
    {
        return [['name' => 'status', 'label' => 'Status', 'options' => OrderStatus::options()]];
    }

    protected function columns(): array
    {
        return [Column::make('reference', 'Reference'), Column::make('customer_name', 'Customer'), Column::make('total_display', 'Total'), Column::make('status', 'Status'), Column::make('type', 'Type'), Column::make('created_at', 'Placed')];
    }

    protected function row(Model $record): array
    {
        return array_merge(parent::row($record), [
            'total_display' => Money::format($record->total, $record->currency),
            'type' => $record->requires_shipping ? 'Print' : 'Digital',
        ]);
    }

    protected function hasForm(): bool
    {
        return false;
    }

    protected function showUrl(Model $record): ?string
    {
        return route('admin.orders.show', $record);
    }

    protected function fields(?Model $record): array
    {
        return [];
    }

    protected function rules(?Model $record): array
    {
        return [];
    }

    public function show(Order $order): Response
    {
        $order->load(['items', 'transactions.refunds', 'statusHistory.user', 'downloads.item', 'downloads.events', 'notes.user']);

        $statusOptions = $order->isPaid()
            ? array_values(array_filter(OrderStatus::options(), fn ($o) => in_array($o['value'], [...self::FULFILMENT_STATUSES, 'cancelled'], true)))
            : array_values(array_filter(OrderStatus::options(), fn ($o) => $o['value'] === 'cancelled'));

        return Inertia::render('admin/orders/show', [
            'order' => [
                ...$order->only(['id', 'reference', 'customer_name', 'email', 'phone', 'currency', 'subtotal', 'delivery_fee', 'total', 'requires_shipping', 'delivery_option_name', 'delivery_estimate',
                    'shipping_address_line1', 'shipping_address_line2', 'shipping_city', 'shipping_state', 'shipping_postal_code', 'shipping_country', 'shipping_carrier', 'shipping_reference', 'customer_notes']),
                'status' => $order->status->value,
                'status_label' => $order->status->label(),
                'is_paid' => $order->isPaid(),
                'created_at' => $order->created_at->toIso8601String(),
                'paid_at' => $order->paid_at?->toIso8601String(),
                'items' => $order->items->map->only(['id', 'title', 'format_label', 'is_digital', 'unit_price', 'quantity', 'line_total']),
                'transactions' => $order->transactions->map(fn ($t) => [
                    'id' => $t->id, 'reference' => $t->reference, 'gateway' => $t->gateway, 'amount' => $t->amount, 'currency' => $t->currency,
                    'status' => $t->status->value, 'status_label' => $t->status->label(), 'channel' => $t->channel, 'failure_reason' => $t->failure_reason,
                    'verified_at' => $t->verified_at?->toIso8601String(), 'created_at' => $t->created_at->toIso8601String(),
                    'refunded' => $t->refunds->sum('amount'),
                ]),
                'downloads' => $order->downloads->map(fn (DigitalDownload $d) => [
                    'id' => $d->id, 'title' => $d->item?->title, 'download_count' => $d->download_count, 'max_downloads' => $d->max_downloads,
                    'expires_at' => $d->expires_at->toIso8601String(), 'revoked' => $d->revoked_at !== null,
                    'status' => $d->unavailableReason() ?? 'active', 'last_downloaded_at' => $d->last_downloaded_at?->toIso8601String(),
                ]),
                'history' => $order->statusHistory->map(fn ($h) => ['to' => OrderStatus::tryFrom($h->to_status)?->label() ?? $h->to_status, 'note' => $h->note, 'user' => $h->user?->name, 'at' => $h->created_at?->toIso8601String()]),
                'notes' => $order->notes->map(fn ($n) => ['id' => $n->id, 'body' => $n->body, 'user' => $n->user?->name, 'at' => $n->created_at->toIso8601String()]),
            ],
            'statusOptions' => $statusOptions,
            'canRefund' => auth()->user()->can('manage refunds'),
        ]);
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $allowed = $order->isPaid() ? [...self::FULFILMENT_STATUSES, 'cancelled'] : ['cancelled'];
        $data = $request->validate([
            'status' => ['required', Rule::in($allowed)],
            'note' => ['nullable', 'string', 'max:255'],
            'notify' => ['boolean'],
        ]);

        $order->transitionTo(OrderStatus::from($data['status']), $data['note'] ?? null, $request->user()->id);
        AuditLog::record('order.status', "Order {$order->reference} set to {$order->status->label()}", $order);

        if ($data['notify'] ?? false) {
            Notification::route('mail', $order->email)->notify(new OrderStatusUpdated($order));
        }

        return back()->with('success', 'Order status updated.');
    }

    public function updateShipping(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'shipping_carrier' => ['nullable', 'string', 'max:120'],
            'shipping_reference' => ['nullable', 'string', 'max:120'],
        ]);
        $order->update($data);
        AuditLog::record('order.shipping', "Shipping details updated for {$order->reference}", $order);

        return back()->with('success', 'Shipping details saved.');
    }

    /**
     * Record a refund issued in the payment provider's dashboard. This does not move money itself.
     */
    public function refund(Request $request, Order $order): RedirectResponse
    {
        $transaction = $order->transactions()->where('status', TransactionStatus::Success)->latest()->firstOrFail();
        $remaining = $transaction->amount - $transaction->refunds()->sum('amount');

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:'.($remaining / 100)],
            'reason' => ['required', 'string', 'max:255'],
            'gateway_reference' => ['nullable', 'string', 'max:120'],
        ]);

        DB::transaction(function () use ($order, $transaction, $data, $remaining, $request) {
            $amount = Money::toMinor($data['amount']);
            $transaction->refunds()->create([
                'amount' => $amount, 'currency' => $transaction->currency, 'reason' => $data['reason'],
                'status' => 'completed', 'gateway_reference' => $data['gateway_reference'] ?? null, 'user_id' => $request->user()->id,
            ]);

            $full = $amount >= $remaining;
            if ($full) {
                $transaction->update(['status' => TransactionStatus::Refunded]);
            }
            $order->transitionTo($full ? OrderStatus::Refunded : OrderStatus::PartiallyRefunded, "Refund recorded: {$data['reason']}", $request->user()->id);

            // A refunded order should not keep download access.
            if ($full) {
                $order->downloads()->update(['revoked_at' => now()]);
            }
        });

        AuditLog::record('order.refund', "Refund of {$order->currency} {$data['amount']} recorded for {$order->reference}", $order);

        return back()->with('success', 'Refund recorded.');
    }

    public function renewDownload(DigitalDownload $download, OrderFulfilmentService $fulfilment): RedirectResponse
    {
        abort_unless($download->order->isPaid(), 422);
        $fulfilment->renewDownload($download);
        AuditLog::record('download.renewed', "Download access renewed for {$download->order->reference}", $download);

        return back()->with('success', 'Download access renewed. Send the customer their order link to download again.');
    }

    public function revokeDownload(DigitalDownload $download): RedirectResponse
    {
        $download->update(['revoked_at' => now()]);
        AuditLog::record('download.revoked', "Download access revoked for {$download->order->reference}", $download);

        return back()->with('success', 'Download access revoked.');
    }

    public function resendReceipt(Order $order): RedirectResponse
    {
        abort_unless($order->isPaid(), 422);
        Notification::route('mail', [$order->email => $order->customer_name])->notify(new OrderPaid($order));

        return back()->with('success', 'Receipt re-sent to '.$order->email.'.');
    }
}

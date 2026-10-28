import { NotesPanel, type Note } from '@/components/admin/notes-panel';
import { Panel } from '@/components/admin/panel';
import { StatusBadge, toneFor } from '@/components/admin/status-badge';
import { Timeline } from '@/components/admin/timeline';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { NativeSelect } from '@/components/ui/native-select';
import AdminLayout from '@/layouts/admin-layout';
import { formatDateTime, formatMoney } from '@/lib/format';
import { router, useForm } from '@inertiajs/react';

interface Order {
    id: number;
    reference: string;
    customer_name: string;
    email: string;
    phone: string | null;
    currency: string;
    subtotal: number;
    delivery_fee: number;
    total: number;
    status: string;
    status_label: string;
    is_paid: boolean;
    requires_shipping: boolean;
    delivery_option_name: string | null;
    delivery_estimate: string | null;
    shipping_address_line1: string | null;
    shipping_address_line2: string | null;
    shipping_city: string | null;
    shipping_state: string | null;
    shipping_postal_code: string | null;
    shipping_country: string | null;
    shipping_carrier: string | null;
    shipping_reference: string | null;
    customer_notes: string | null;
    created_at: string;
    paid_at: string | null;
    items: { id: number; title: string; format_label: string; is_digital: boolean; unit_price: number; quantity: number; line_total: number }[];
    transactions: {
        id: number;
        reference: string;
        gateway: string;
        amount: number;
        currency: string;
        status: string;
        status_label: string;
        channel: string | null;
        failure_reason: string | null;
        verified_at: string | null;
        created_at: string;
        refunded: number;
    }[];
    downloads: {
        id: number;
        title: string | null;
        download_count: number;
        max_downloads: number;
        expires_at: string;
        revoked: boolean;
        status: string;
        last_downloaded_at: string | null;
    }[];
    history: { to: string; note: string | null; user: string | null; at: string | null }[];
    notes: Note[];
}

export default function OrderShow({
    order,
    statusOptions,
    canRefund,
}: {
    order: Order;
    statusOptions: { value: string; label: string }[];
    canRefund: boolean;
}) {
    const status = useForm({ status: statusOptions[0]?.value ?? '', note: '', notify: true as boolean });
    const shipping = useForm({ shipping_carrier: order.shipping_carrier ?? '', shipping_reference: order.shipping_reference ?? '' });
    const refund = useForm({ amount: '', reason: '', gateway_reference: '' });
    const paid = order.transactions.find((t) => t.status === 'success');

    return (
        <AdminLayout
            title={`Order ${order.reference}`}
            description={`Placed ${formatDateTime(order.created_at)}${order.paid_at ? ` · paid ${formatDateTime(order.paid_at)}` : ''}`}
            breadcrumbs={[
                { title: 'Orders', href: '/admin/orders' },
                { title: order.reference, href: '#' },
            ]}
            actions={
                <>
                    <StatusBadge label={order.status_label} tone={toneFor(order.status)} className="px-3 py-1 text-sm" />
                    {order.is_paid && (
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() => router.post(route('admin.orders.resend', order.reference), {}, { preserveScroll: true })}
                        >
                            Resend receipt
                        </Button>
                    )}
                </>
            }
        >
            <div className="grid gap-6 xl:grid-cols-[1.5fr_1fr]">
                <div className="space-y-6">
                    <Panel title="Items">
                        <table className="w-full text-sm">
                            <tbody className="divide-border divide-y">
                                {order.items.map((i) => (
                                    <tr key={i.id}>
                                        <td className="py-2">
                                            {i.title} <span className="text-muted-foreground">· {i.format_label}</span>
                                        </td>
                                        <td className="py-2 text-right whitespace-nowrap">
                                            {i.quantity} × {formatMoney(i.unit_price, order.currency)}
                                        </td>
                                        <td className="py-2 text-right font-medium whitespace-nowrap">{formatMoney(i.line_total, order.currency)}</td>
                                    </tr>
                                ))}
                            </tbody>
                            <tfoot className="border-border border-t">
                                <tr>
                                    <td colSpan={2} className="pt-3 text-right">
                                        Subtotal
                                    </td>
                                    <td className="pt-3 text-right">{formatMoney(order.subtotal, order.currency)}</td>
                                </tr>
                                {order.requires_shipping && (
                                    <tr>
                                        <td colSpan={2} className="text-right">
                                            Delivery ({order.delivery_option_name})
                                        </td>
                                        <td className="text-right">{formatMoney(order.delivery_fee, order.currency)}</td>
                                    </tr>
                                )}
                                <tr className="font-semibold">
                                    <td colSpan={2} className="pt-1 text-right">
                                        Total
                                    </td>
                                    <td className="pt-1 text-right">{formatMoney(order.total, order.currency)}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </Panel>

                    <Panel title="Payments" description="Only server-verified transactions count as paid.">
                        {order.transactions.length === 0 ? (
                            <p className="text-muted-foreground text-sm">No payment attempts.</p>
                        ) : (
                            <ul className="divide-border divide-y text-sm">
                                {order.transactions.map((t) => (
                                    <li key={t.id} className="flex flex-wrap items-center justify-between gap-2 py-2">
                                        <span className="font-mono text-xs">{t.reference}</span>
                                        <span className="text-muted-foreground">
                                            {t.gateway}
                                            {t.channel && ` · ${t.channel}`}
                                        </span>
                                        <span>{formatMoney(t.amount, t.currency)}</span>
                                        <StatusBadge label={t.status_label} tone={toneFor(t.status)} />
                                        {t.failure_reason && <span className="text-destructive w-full text-xs">{t.failure_reason}</span>}
                                        {t.refunded > 0 && <span className="w-full text-xs">Refunded: {formatMoney(t.refunded, t.currency)}</span>}
                                    </li>
                                ))}
                            </ul>
                        )}
                    </Panel>

                    {order.downloads.length > 0 && (
                        <Panel title="Digital downloads">
                            <ul className="divide-border divide-y text-sm">
                                {order.downloads.map((d) => (
                                    <li key={d.id} className="flex flex-wrap items-center gap-3 py-2">
                                        <span className="flex-1 font-medium">{d.title}</span>
                                        <span className="text-muted-foreground text-xs">
                                            {d.download_count}/{d.max_downloads} used · expires {formatDateTime(d.expires_at)}
                                        </span>
                                        <StatusBadge label={d.status.replace('_', ' ')} tone={toneFor(d.status)} />
                                        <Button
                                            size="sm"
                                            variant="outline"
                                            onClick={() => router.post(route('admin.downloads.renew', d.id), {}, { preserveScroll: true })}
                                        >
                                            Renew
                                        </Button>
                                        {!d.revoked && (
                                            <Button
                                                size="sm"
                                                variant="ghost"
                                                className="text-destructive"
                                                onClick={() => router.post(route('admin.downloads.revoke', d.id), {}, { preserveScroll: true })}
                                            >
                                                Revoke
                                            </Button>
                                        )}
                                    </li>
                                ))}
                            </ul>
                        </Panel>
                    )}

                    <NotesPanel notes={order.notes} notableType="order" notableId={order.id} />
                </div>

                <div className="space-y-6">
                    <Panel title="Customer">
                        <p className="font-medium">{order.customer_name}</p>
                        <p className="text-sm">
                            <a href={`mailto:${order.email}`} className="underline">
                                {order.email}
                            </a>
                        </p>
                        {order.phone && <p className="text-sm">{order.phone}</p>}
                        {order.requires_shipping && (
                            <address className="text-muted-foreground mt-3 text-sm not-italic">
                                {[
                                    order.shipping_address_line1,
                                    order.shipping_address_line2,
                                    order.shipping_city,
                                    order.shipping_state,
                                    order.shipping_postal_code,
                                    order.shipping_country,
                                ]
                                    .filter(Boolean)
                                    .map((l) => (
                                        <span key={l} className="block">
                                            {l}
                                        </span>
                                    ))}
                            </address>
                        )}
                        {order.customer_notes && <p className="bg-muted mt-3 rounded p-2 text-sm">“{order.customer_notes}”</p>}
                    </Panel>

                    {statusOptions.length > 0 && (
                        <Panel title="Update status">
                            <form
                                onSubmit={(e) => {
                                    e.preventDefault();
                                    status.put(route('admin.orders.status', order.reference), {
                                        preserveScroll: true,
                                        onSuccess: () => status.reset('note'),
                                    });
                                }}
                                className="space-y-3"
                            >
                                <NativeSelect
                                    aria-label="New status"
                                    value={status.data.status}
                                    onChange={(e) => status.setData('status', e.target.value)}
                                >
                                    {statusOptions.map((o) => (
                                        <option key={o.value} value={o.value}>
                                            {o.label}
                                        </option>
                                    ))}
                                </NativeSelect>
                                <Input
                                    aria-label="Note"
                                    placeholder="Note (optional)"
                                    value={status.data.note}
                                    onChange={(e) => status.setData('note', e.target.value)}
                                />
                                <label className="flex items-center gap-2 text-sm">
                                    <input
                                        type="checkbox"
                                        className="accent-brand-800"
                                        checked={status.data.notify}
                                        onChange={(e) => status.setData('notify', e.target.checked)}
                                    />{' '}
                                    Email the customer
                                </label>
                                <Button type="submit" size="sm" disabled={status.processing}>
                                    Update
                                </Button>
                                {!order.is_paid && (
                                    <p className="text-muted-foreground text-xs">
                                        Unpaid orders can only be cancelled. Paid status is set automatically when a payment is verified.
                                    </p>
                                )}
                            </form>
                        </Panel>
                    )}

                    {order.requires_shipping && order.is_paid && (
                        <Panel title="Shipping">
                            <form
                                onSubmit={(e) => {
                                    e.preventDefault();
                                    shipping.put(route('admin.orders.shipping', order.reference), { preserveScroll: true });
                                }}
                                className="space-y-3"
                            >
                                <Input
                                    aria-label="Carrier"
                                    placeholder="Carrier / courier"
                                    value={shipping.data.shipping_carrier}
                                    onChange={(e) => shipping.setData('shipping_carrier', e.target.value)}
                                />
                                <Input
                                    aria-label="Tracking reference"
                                    placeholder="Tracking reference"
                                    value={shipping.data.shipping_reference}
                                    onChange={(e) => shipping.setData('shipping_reference', e.target.value)}
                                />
                                <Button type="submit" size="sm" variant="outline" disabled={shipping.processing}>
                                    Save shipping
                                </Button>
                            </form>
                        </Panel>
                    )}

                    {canRefund && paid && (
                        <Panel title="Record a refund" description="Issue the refund in the payment provider's dashboard first, then record it here.">
                            <form
                                onSubmit={(e) => {
                                    e.preventDefault();
                                    refund.post(route('admin.orders.refund', order.reference), {
                                        preserveScroll: true,
                                        onSuccess: () => refund.reset(),
                                    });
                                }}
                                className="space-y-3"
                            >
                                <Input
                                    aria-label="Amount"
                                    type="number"
                                    step="0.01"
                                    min={0}
                                    placeholder={`Amount (${order.currency})`}
                                    value={refund.data.amount}
                                    onChange={(e) => refund.setData('amount', e.target.value)}
                                />
                                {refund.errors.amount && <p className="text-destructive text-xs">{refund.errors.amount}</p>}
                                <Input
                                    aria-label="Reason"
                                    placeholder="Reason"
                                    value={refund.data.reason}
                                    onChange={(e) => refund.setData('reason', e.target.value)}
                                />
                                <Input
                                    aria-label="Provider refund reference"
                                    placeholder="Provider refund reference (optional)"
                                    value={refund.data.gateway_reference}
                                    onChange={(e) => refund.setData('gateway_reference', e.target.value)}
                                />
                                <Button type="submit" size="sm" variant="destructive" disabled={refund.processing}>
                                    Record refund
                                </Button>
                            </form>
                        </Panel>
                    )}

                    <Panel title="History">
                        <Timeline items={order.history} />
                    </Panel>
                </div>
            </div>
        </AdminLayout>
    );
}

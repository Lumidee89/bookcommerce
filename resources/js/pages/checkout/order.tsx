import { Container } from '@/components/site/section';
import PublicLayout from '@/layouts/public-layout';
import { toValue, trackOnce } from '@/lib/analytics';
import { formatDate, formatDateTime, formatMoney } from '@/lib/format';
import { cn } from '@/lib/utils';
import { type SharedData } from '@/types';
import { router, usePage } from '@inertiajs/react';
import { CheckCircle2, Clock, Download, Package, XCircle } from 'lucide-react';
import { useEffect } from 'react';

interface OrderView {
    reference: string;
    status: string;
    status_label: string;
    is_paid: boolean;
    customer_name: string;
    email: string;
    currency: string;
    subtotal: number;
    delivery_fee: number;
    total: number;
    requires_shipping: boolean;
    delivery_option_name: string | null;
    delivery_estimate: string | null;
    shipping_reference: string | null;
    shipping_carrier: string | null;
    created_at: string;
    items: {
        title: string;
        format_label: string;
        quantity: number;
        line_total: number;
        is_digital: boolean;
        download: { url: string; available: boolean; remaining: number; expires_at: string } | null;
    }[];
}

export default function OrderPage({ order, retryUrl }: { order: OrderView; retryUrl: string }) {
    const { payments } = usePage<SharedData>().props;
    const pending = order.status === 'pending_payment';

    // Counted once per order, and only after the server has verified payment.
    useEffect(() => {
        if (!order.is_paid) return;
        trackOnce(`purchase:${order.reference}`, 'purchase', {
            transaction_id: order.reference,
            currency: order.currency,
            value: toValue(order.total),
            shipping: toValue(order.delivery_fee),
            items: order.items.map((i) => ({
                item_name: i.title,
                item_variant: i.format_label,
                quantity: i.quantity,
                price: toValue(i.line_total / i.quantity),
            })),
        });
    }, [order]);

    // If the provider's confirmation is still on its way, check again shortly.
    useEffect(() => {
        if (!pending) return;
        const t = setTimeout(() => router.reload({ only: ['order'] }), 5000);
        return () => clearTimeout(t);
    }, [pending, order.status]);

    return (
        <PublicLayout newsletter={false}>
            <Container className="max-w-3xl py-14">
                <div
                    className={cn(
                        'flex items-start gap-4 rounded-2xl border p-6',
                        order.is_paid
                            ? 'border-brand-200 bg-brand-50 text-brand-900'
                            : order.status === 'payment_failed' || order.status === 'cancelled'
                              ? 'border-red-200 bg-red-50 text-red-900'
                              : 'border-gold-200 bg-gold-50 text-gold-900',
                    )}
                >
                    {order.is_paid ? (
                        <CheckCircle2 className="size-8 shrink-0" />
                    ) : pending ? (
                        <Clock className="size-8 shrink-0" />
                    ) : (
                        <XCircle className="size-8 shrink-0" />
                    )}
                    <div>
                        <h1 className="text-3xl font-semibold">
                            {order.is_paid
                                ? `Thank you, ${order.customer_name.split(' ')[0]}!`
                                : pending
                                  ? 'Awaiting payment confirmation'
                                  : 'Payment not completed'}
                        </h1>
                        <p className="mt-1 text-sm">
                            Order {order.reference} · {order.status_label} · placed {formatDate(order.created_at)}
                        </p>
                        {order.is_paid && <p className="mt-2 text-sm">A receipt has been sent to {order.email}.</p>}
                    </div>
                </div>

                {!order.is_paid && order.status !== 'cancelled' && payments.enabled && (
                    <button
                        onClick={() => router.post(retryUrl)}
                        className="bg-primary text-primary-foreground mt-6 w-full rounded-md px-6 py-3 font-medium"
                    >
                        {pending ? 'Complete payment' : 'Try payment again'} · {formatMoney(order.total, order.currency)}
                    </button>
                )}

                <section className="border-border bg-card mt-8 rounded-xl border p-6">
                    <h2 className="text-2xl font-semibold">Items</h2>
                    <ul className="divide-border mt-4 divide-y">
                        {order.items.map((item, i) => (
                            <li key={i} className="py-4">
                                <div className="flex justify-between gap-4">
                                    <div>
                                        <p className="font-medium">{item.title}</p>
                                        <p className="text-muted-foreground flex items-center gap-1.5 text-sm">
                                            {item.is_digital ? <Download className="size-3.5" /> : <Package className="size-3.5" />}
                                            {item.format_label} × {item.quantity}
                                        </p>
                                    </div>
                                    <p className="font-medium">{formatMoney(item.line_total, order.currency)}</p>
                                </div>
                                {item.download && (
                                    <div className="mt-3">
                                        {item.download.available ? (
                                            <a
                                                href={item.download.url}
                                                className="bg-gold-600 hover:bg-gold-700 inline-flex items-center gap-2 rounded-md px-4 py-2 text-sm font-medium text-white"
                                            >
                                                <Download className="size-4" /> Download
                                            </a>
                                        ) : (
                                            <a href={item.download.url} className="text-sm underline">
                                                Download unavailable — request a new link
                                            </a>
                                        )}
                                        <p className="text-muted-foreground mt-1 text-xs">
                                            {item.download.remaining} download{item.download.remaining === 1 ? '' : 's'} remaining · link expires{' '}
                                            {formatDateTime(item.download.expires_at)}
                                        </p>
                                    </div>
                                )}
                            </li>
                        ))}
                    </ul>
                    <dl className="border-border space-y-1 border-t pt-4 text-sm">
                        <div className="flex justify-between">
                            <dt>Subtotal</dt>
                            <dd>{formatMoney(order.subtotal, order.currency)}</dd>
                        </div>
                        {order.requires_shipping && (
                            <div className="flex justify-between">
                                <dt>Delivery ({order.delivery_option_name})</dt>
                                <dd>{formatMoney(order.delivery_fee, order.currency)}</dd>
                            </div>
                        )}
                        <div className="flex justify-between pt-1 text-base font-semibold">
                            <dt>Total</dt>
                            <dd>{formatMoney(order.total, order.currency)}</dd>
                        </div>
                    </dl>
                </section>

                {order.requires_shipping && order.is_paid && (
                    <section className="border-border mt-6 rounded-xl border p-6 text-sm">
                        <h2 className="text-xl font-semibold">Delivery</h2>
                        <p className="mt-2">
                            {order.delivery_estimate ? `Estimated delivery: ${order.delivery_estimate}.` : 'We will be in touch about delivery.'}
                        </p>
                        {order.shipping_reference && (
                            <p className="mt-1">
                                Tracking:{' '}
                                <span className="font-medium">{[order.shipping_carrier, order.shipping_reference].filter(Boolean).join(' ')}</span>
                            </p>
                        )}
                    </section>
                )}
                <p className="text-muted-foreground mt-8 text-center text-xs">
                    Keep this page's link private — it gives access to your order and downloads.
                </p>
            </Container>
        </PublicLayout>
    );
}

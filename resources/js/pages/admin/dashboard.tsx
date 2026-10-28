import { Panel } from '@/components/admin/panel';
import { StatusBadge, toneFor } from '@/components/admin/status-badge';
import AdminLayout from '@/layouts/admin-layout';
import { formatDate, formatDateTime, formatMoney } from '@/lib/format';
import { cn } from '@/lib/utils';
import { Link, router } from '@inertiajs/react';
import { AlertTriangle, CalendarClock, Inbox, Mail, Mic2, Package, ShoppingCart, Undo2 } from 'lucide-react';
import { ReactNode } from 'react';

type Sum = { currency: string; total: number; count: number };

interface Props {
    range: number;
    from: string;
    to: string;
    sales?: {
        revenue: Sum[];
        consultation_revenue: Sum[];
        refunds: Sum[];
        pending_transactions: number;
        failed_transactions: number;
        awaiting_fulfilment: number;
        recent_orders: {
            reference: string;
            customer: string;
            total: number;
            currency: string;
            status: string;
            status_label: string;
            url: string;
            at: string;
        }[];
    };
    bookings?: {
        upcoming: number;
        pending_payment: number;
        attention: number;
        next: { reference: string; name: string; service: string; starts_at: string; url: string }[];
    };
    inbox?: { new_invitations: number; unresolved_enquiries: number };
    subscribers?: { active: number; new_in_range: number };
    activity?: { description: string; user: string; at: string }[];
}

function Stat({
    label,
    value,
    hint,
    icon,
    href,
    alert,
}: {
    label: string;
    value: ReactNode;
    hint?: string;
    icon: ReactNode;
    href?: string;
    alert?: boolean;
}) {
    const body = (
        <div
            className={cn(
                'border-border bg-card h-full rounded-xl border p-5 transition-colors',
                href && 'hover:border-gold-400',
                alert && 'border-amber-300 bg-amber-50/60 dark:bg-amber-500/5',
            )}
        >
            <div className="text-muted-foreground flex items-center justify-between text-xs font-medium tracking-wide uppercase">
                {label}
                <span className="text-gold-600">{icon}</span>
            </div>
            <div className="mt-3 font-serif text-3xl font-semibold">{value}</div>
            {hint && <p className="text-muted-foreground mt-1 text-xs">{hint}</p>}
        </div>
    );
    return href ? (
        <Link href={href} className="block">
            {body}
        </Link>
    ) : (
        body
    );
}

const money = (sums: Sum[]) => (sums.length ? sums.map((s) => formatMoney(Number(s.total), s.currency)).join(' · ') : formatMoney(0, 'NGN'));
const count = (sums: Sum[]) => sums.reduce((n, s) => n + Number(s.count), 0);

export default function Dashboard({ range, from, to, sales, bookings, inbox, subscribers, activity }: Props) {
    return (
        <AdminLayout
            title="Dashboard"
            description={`Figures from verified records, ${formatDate(from)} – ${formatDate(to)}.`}
            actions={
                <select
                    aria-label="Date range"
                    value={range}
                    onChange={(e) => router.get('/admin', { range: e.target.value }, { preserveState: true })}
                    className="border-input bg-background h-9 rounded-md border px-2 text-sm"
                >
                    <option value={7}>Last 7 days</option>
                    <option value={30}>Last 30 days</option>
                    <option value={90}>Last 90 days</option>
                    <option value={365}>Last 12 months</option>
                </select>
            }
        >
            <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                {sales && (
                    <>
                        <Stat
                            label="Book sales"
                            value={money(sales.revenue)}
                            hint={`${count(sales.revenue)} successful payments · excludes pending & failed`}
                            icon={<ShoppingCart className="size-4" />}
                            href="/admin/orders"
                        />
                        <Stat
                            label="Awaiting fulfilment"
                            value={sales.awaiting_fulfilment}
                            hint="Paid print orders not yet shipped"
                            icon={<Package className="size-4" />}
                            href="/admin/orders?status=paid"
                            alert={sales.awaiting_fulfilment > 0}
                        />
                        <Stat
                            label="Consultation fees"
                            value={money(sales.consultation_revenue)}
                            hint={`${count(sales.consultation_revenue)} paid bookings`}
                            icon={<CalendarClock className="size-4" />}
                        />
                        <Stat
                            label="Refunds recorded"
                            value={money(sales.refunds)}
                            hint={`${sales.pending_transactions} pending · ${sales.failed_transactions} failed/abandoned payments`}
                            icon={<Undo2 className="size-4" />}
                        />
                    </>
                )}
                {bookings && (
                    <>
                        <Stat
                            label="Upcoming consultations"
                            value={bookings.upcoming}
                            icon={<CalendarClock className="size-4" />}
                            href="/admin/bookings?status=confirmed"
                        />
                        <Stat
                            label="Pending consultation payments"
                            value={bookings.pending_payment}
                            hint="Holds awaiting payment"
                            icon={<CalendarClock className="size-4" />}
                            href="/admin/bookings?status=pending_payment"
                        />
                    </>
                )}
                {bookings && bookings.attention > 0 && (
                    <Stat
                        label="Bookings need attention"
                        value={bookings.attention}
                        hint="Paid after hold expired"
                        icon={<AlertTriangle className="size-4" />}
                        href="/admin/bookings?status=requires_attention"
                        alert
                    />
                )}
                {inbox && (
                    <>
                        <Stat
                            label="New speaking invitations"
                            value={inbox.new_invitations}
                            icon={<Mic2 className="size-4" />}
                            href="/admin/invitations?status=new"
                            alert={inbox.new_invitations > 0}
                        />
                        <Stat
                            label="Unresolved enquiries"
                            value={inbox.unresolved_enquiries}
                            icon={<Inbox className="size-4" />}
                            href="/admin/enquiries"
                        />
                    </>
                )}
                {subscribers && (
                    <Stat
                        label="Active subscribers"
                        value={subscribers.active}
                        hint={`${subscribers.new_in_range} confirmed in this period`}
                        icon={<Mail className="size-4" />}
                        href="/admin/subscribers"
                    />
                )}
            </div>

            <div className="grid gap-6 lg:grid-cols-2">
                {sales && (
                    <Panel
                        title="Recent orders"
                        actions={
                            <Link href="/admin/orders" className="text-sm underline">
                                All orders
                            </Link>
                        }
                    >
                        {sales.recent_orders.length === 0 ? (
                            <p className="text-muted-foreground text-sm">No orders yet.</p>
                        ) : (
                            <ul className="divide-border -my-2 divide-y">
                                {sales.recent_orders.map((o) => (
                                    <li key={o.reference}>
                                        <Link
                                            href={o.url}
                                            className="hover:bg-muted/50 -mx-2 flex items-center justify-between gap-3 rounded px-2 py-2.5 text-sm"
                                        >
                                            <span>
                                                <span className="font-medium">{o.reference}</span>{' '}
                                                <span className="text-muted-foreground">· {o.customer}</span>
                                            </span>
                                            <span className="flex items-center gap-3">
                                                <span>{formatMoney(o.total, o.currency)}</span>
                                                <StatusBadge label={o.status_label} tone={toneFor(o.status)} />
                                            </span>
                                        </Link>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </Panel>
                )}
                {bookings && (
                    <Panel
                        title="Next consultations"
                        actions={
                            <Link href="/admin/bookings" className="text-sm underline">
                                All bookings
                            </Link>
                        }
                    >
                        {bookings.next.length === 0 ? (
                            <p className="text-muted-foreground text-sm">Nothing scheduled.</p>
                        ) : (
                            <ul className="divide-border -my-2 divide-y">
                                {bookings.next.map((b) => (
                                    <li key={b.reference}>
                                        <Link
                                            href={b.url}
                                            className="hover:bg-muted/50 -mx-2 flex items-center justify-between gap-3 rounded px-2 py-2.5 text-sm"
                                        >
                                            <span>
                                                <span className="font-medium">{b.name}</span>{' '}
                                                <span className="text-muted-foreground">· {b.service}</span>
                                            </span>
                                            <span className="text-muted-foreground shrink-0">{formatDateTime(b.starts_at)}</span>
                                        </Link>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </Panel>
                )}
                {activity && (
                    <Panel title="Recent administrative activity" className="lg:col-span-2">
                        {activity.length === 0 ? (
                            <p className="text-muted-foreground text-sm">No activity recorded yet.</p>
                        ) : (
                            <ul className="space-y-2 text-sm">
                                {activity.map((a, i) => (
                                    <li key={i} className="flex justify-between gap-4">
                                        <span>{a.description}</span>
                                        <span className="text-muted-foreground shrink-0 text-xs">
                                            {a.user} · {formatDateTime(a.at)}
                                        </span>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </Panel>
                )}
            </div>
        </AdminLayout>
    );
}

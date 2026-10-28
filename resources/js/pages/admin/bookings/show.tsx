import { NotesPanel, type Note } from '@/components/admin/notes-panel';
import { Panel } from '@/components/admin/panel';
import { StatusBadge, toneFor } from '@/components/admin/status-badge';
import { Timeline } from '@/components/admin/timeline';
import { SlotPicker } from '@/components/site/slot-picker';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import AdminLayout from '@/layouts/admin-layout';
import { formatDateTime, formatMoney } from '@/lib/format';
import { type BookingSummary } from '@/types';
import { useForm } from '@inertiajs/react';
import { useState } from 'react';

type Booking = BookingSummary & {
    id: number;
    phone: string | null;
    organisation: string | null;
    needs: string | null;
    cancellation_reason: string | null;
    reschedule_count: number;
    fee_display: string;
    transactions: { reference: string; amount: number; currency: string; status: string; status_label: string; created_at: string }[];
    history: { to: string; note: string | null; user: string | null; at: string | null }[];
    notes: Note[];
};

export default function BookingShow({
    booking,
    service,
    ownerTimezone,
}: {
    booking: Booking;
    service: { slug: string; duration_minutes: number } | null;
    ownerTimezone: string;
}) {
    const meeting = useForm({ meeting_url: booking.meeting_url ?? '', meeting_details: booking.meeting_details ?? '', notify: true as boolean });
    const status = useForm({ status: 'completed', reason: '' });
    const reschedule = useForm({ starts_at: null as string | null });
    const [showReschedule, setShowReschedule] = useState(false);
    const active = ['confirmed', 'requires_attention'].includes(booking.status);

    return (
        <AdminLayout
            title={`${booking.name} — ${booking.service_name}`}
            description={`Booking ${booking.reference}`}
            breadcrumbs={[
                { title: 'Bookings', href: '/admin/bookings' },
                { title: booking.reference, href: '#' },
            ]}
            actions={<StatusBadge label={booking.status_label} tone={toneFor(booking.status)} className="px-3 py-1 text-sm" />}
        >
            <div className="grid gap-6 xl:grid-cols-[1.5fr_1fr]">
                <div className="space-y-6">
                    <Panel title="Appointment">
                        <dl className="grid gap-4 text-sm sm:grid-cols-2">
                            <div>
                                <dt className="text-muted-foreground">Your time ({ownerTimezone})</dt>
                                <dd className="font-medium">{formatDateTime(booking.starts_at, ownerTimezone)}</dd>
                            </div>
                            <div>
                                <dt className="text-muted-foreground">Client's time ({booking.client_timezone})</dt>
                                <dd className="font-medium">{formatDateTime(booking.starts_at, booking.client_timezone)}</dd>
                            </div>
                            <div>
                                <dt className="text-muted-foreground">Fee</dt>
                                <dd className="font-medium">{booking.fee_display}</dd>
                            </div>
                            <div>
                                <dt className="text-muted-foreground">Rescheduled</dt>
                                <dd className="font-medium">{booking.reschedule_count} time(s)</dd>
                            </div>
                        </dl>
                        {booking.needs && (
                            <div className="mt-5">
                                <p className="text-muted-foreground text-sm">Client's notes</p>
                                <p className="bg-muted mt-1 rounded p-3 text-sm whitespace-pre-line">{booking.needs}</p>
                            </div>
                        )}
                        {booking.cancellation_reason && (
                            <p className="text-destructive mt-4 text-sm">Cancellation reason: {booking.cancellation_reason}</p>
                        )}
                    </Panel>

                    {active && (
                        <Panel title="Meeting details" description="Shown to the client once the booking is confirmed.">
                            <form
                                onSubmit={(e) => {
                                    e.preventDefault();
                                    meeting.put(route('admin.bookings.meeting', booking.reference), { preserveScroll: true });
                                }}
                                className="space-y-3"
                            >
                                <Input
                                    aria-label="Meeting link"
                                    type="url"
                                    placeholder="https://meet…"
                                    value={meeting.data.meeting_url}
                                    onChange={(e) => meeting.setData('meeting_url', e.target.value)}
                                />
                                {meeting.errors.meeting_url && <p className="text-destructive text-xs">{meeting.errors.meeting_url}</p>}
                                <Textarea
                                    aria-label="Meeting details"
                                    rows={3}
                                    placeholder="Dial-in, venue address or other instructions"
                                    value={meeting.data.meeting_details}
                                    onChange={(e) => meeting.setData('meeting_details', e.target.value)}
                                />
                                <label className="flex items-center gap-2 text-sm">
                                    <input
                                        type="checkbox"
                                        className="accent-brand-800"
                                        checked={meeting.data.notify}
                                        onChange={(e) => meeting.setData('notify', e.target.checked)}
                                    />{' '}
                                    Email updated details to the client
                                </label>
                                <Button type="submit" size="sm" disabled={meeting.processing}>
                                    Save meeting details
                                </Button>
                            </form>
                        </Panel>
                    )}

                    {active && service && (
                        <Panel
                            title="Reschedule"
                            actions={
                                !showReschedule && (
                                    <Button size="sm" variant="outline" onClick={() => setShowReschedule(true)}>
                                        Choose new time
                                    </Button>
                                )
                            }
                        >
                            {showReschedule ? (
                                <form
                                    onSubmit={(e) => {
                                        e.preventDefault();
                                        reschedule.put(route('admin.bookings.reschedule', booking.reference), {
                                            preserveScroll: true,
                                            onSuccess: () => setShowReschedule(false),
                                        });
                                    }}
                                    className="space-y-4"
                                >
                                    <SlotPicker
                                        serviceSlug={service.slug}
                                        timezone={ownerTimezone}
                                        value={reschedule.data.starts_at}
                                        onChange={(v) => reschedule.setData('starts_at', v)}
                                    />
                                    <Button type="submit" size="sm" disabled={!reschedule.data.starts_at || reschedule.processing}>
                                        Reschedule & notify client
                                    </Button>
                                </form>
                            ) : (
                                <p className="text-muted-foreground text-sm">
                                    Pick a new time from live availability. The client receives a new confirmation.
                                </p>
                            )}
                        </Panel>
                    )}

                    <NotesPanel notes={booking.notes} notableType="booking" notableId={booking.id} />
                </div>

                <div className="space-y-6">
                    <Panel title="Client">
                        <p className="font-medium">{booking.name}</p>
                        <a href={`mailto:${booking.email}`} className="text-sm underline">
                            {booking.email}
                        </a>
                        {booking.phone && <p className="text-sm">{booking.phone}</p>}
                        {booking.organisation && <p className="text-muted-foreground text-sm">{booking.organisation}</p>}
                    </Panel>

                    {(active || booking.status === 'pending_payment') && (
                        <Panel title="Update status">
                            <form
                                onSubmit={(e) => {
                                    e.preventDefault();
                                    status.put(route('admin.bookings.status', booking.reference), { preserveScroll: true });
                                }}
                                className="space-y-3"
                            >
                                <select
                                    aria-label="Status"
                                    value={status.data.status}
                                    onChange={(e) => status.setData('status', e.target.value)}
                                    className="border-input bg-background h-10 w-full rounded-md border px-3 text-sm"
                                >
                                    {booking.status === 'confirmed' && <option value="completed">Completed</option>}
                                    {booking.status === 'confirmed' && <option value="no_show">No show</option>}
                                    <option value="cancelled">Cancel booking (client is emailed)</option>
                                </select>
                                <Input
                                    aria-label="Reason"
                                    placeholder="Reason / note"
                                    value={status.data.reason}
                                    onChange={(e) => status.setData('reason', e.target.value)}
                                />
                                <Button
                                    type="submit"
                                    size="sm"
                                    variant={status.data.status === 'cancelled' ? 'destructive' : 'default'}
                                    disabled={status.processing}
                                >
                                    Update
                                </Button>
                            </form>
                        </Panel>
                    )}

                    {booking.transactions.length > 0 && (
                        <Panel title="Payments">
                            <ul className="space-y-2 text-sm">
                                {booking.transactions.map((t) => (
                                    <li key={t.reference} className="flex items-center justify-between gap-2">
                                        <span className="font-mono text-xs">{t.reference}</span>
                                        <span>{formatMoney(t.amount, t.currency)}</span>
                                        <StatusBadge label={t.status_label} tone={toneFor(t.status)} />
                                    </li>
                                ))}
                            </ul>
                        </Panel>
                    )}

                    <Panel title="History">
                        <Timeline items={booking.history} />
                    </Panel>
                </div>
            </div>
        </AdminLayout>
    );
}

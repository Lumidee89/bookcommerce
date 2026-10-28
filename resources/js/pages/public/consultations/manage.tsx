import { Container } from '@/components/site/section';
import { SlotPicker } from '@/components/site/slot-picker';
import PublicLayout from '@/layouts/public-layout';
import { toValue, trackOnce } from '@/lib/analytics';
import { detectTimeZone, formatDateTime, formatMoney } from '@/lib/format';
import { cn } from '@/lib/utils';
import { type BookingSummary } from '@/types';
import { router, useForm } from '@inertiajs/react';
import { CalendarClock, CheckCircle2, Clock, Video, XCircle } from 'lucide-react';
import { useEffect, useState } from 'react';

interface Props {
    booking: BookingSummary;
    token: string;
    canCancel: boolean;
    canReschedule: boolean;
    canPay: boolean;
    policy: { cancellation: string | null; rescheduling: string | null };
}

export default function ManageBooking({ booking, token, canCancel, canReschedule, canPay, policy }: Props) {
    const [mode, setMode] = useState<'view' | 'cancel' | 'reschedule'>('view');
    const tz = booking.client_timezone || detectTimeZone();
    const cancelForm = useForm({ reason: '', token });
    const rescheduleForm = useForm({ starts_at: null as string | null, timezone: tz, token });

    useEffect(() => {
        if (booking.status === 'confirmed') {
            trackOnce(`booking:${booking.reference}`, 'book_consultation', {
                service: booking.service_name,
                currency: booking.currency,
                value: toValue(booking.fee),
            });
        }
    }, [booking.status, booking.reference, booking.service_name, booking.currency, booking.fee]);

    // Paid bookings may still be awaiting webhook verification; refresh briefly.
    useEffect(() => {
        if (booking.status !== 'pending_payment' || canPay) return;
        const t = setTimeout(() => router.reload({ only: ['booking', 'canPay', 'canCancel', 'canReschedule'] }), 4000);
        return () => clearTimeout(t);
    }, [booking.status, canPay]);

    const tone =
        {
            confirmed: 'bg-brand-50 text-brand-900 border-brand-200',
            pending_payment: 'bg-gold-50 text-gold-800 border-gold-200',
            cancelled: 'bg-red-50 text-red-900 border-red-200',
            expired: 'bg-red-50 text-red-900 border-red-200',
        }[booking.status] ?? 'bg-muted';

    return (
        <PublicLayout newsletter={false}>
            <Container className="max-w-3xl py-14">
                <p className="text-muted-foreground text-sm">Booking {booking.reference}</p>
                <h1 className="text-brand-900 dark:text-foreground mt-1 text-4xl font-semibold sm:text-5xl">{booking.service_name}</h1>

                <div className={cn('mt-6 flex items-start gap-3 rounded-xl border p-5', tone)}>
                    {booking.status === 'confirmed' ? (
                        <CheckCircle2 className="size-6 shrink-0" />
                    ) : booking.status === 'pending_payment' ? (
                        <Clock className="size-6 shrink-0" />
                    ) : (
                        <XCircle className="size-6 shrink-0" />
                    )}
                    <div>
                        <p className="font-semibold">{booking.status_label}</p>
                        <p className="text-sm">
                            {booking.status === 'confirmed' &&
                                'Your consultation is confirmed. A confirmation and calendar invite were emailed to you.'}
                            {booking.status === 'pending_payment' &&
                                (canPay
                                    ? `This time is held for you until ${formatDateTime(booking.hold_expires_at, tz)}. Complete payment to confirm.`
                                    : 'We are confirming your payment…')}
                            {booking.status === 'expired' && 'The reservation expired before payment was completed. Please book a new time.'}
                            {booking.status === 'cancelled' && 'This booking has been cancelled.'}
                            {booking.status === 'requires_attention' &&
                                'Your payment was received but the time is no longer available. We will contact you shortly to arrange a new time.'}
                        </p>
                    </div>
                </div>

                <dl className="border-border bg-card mt-6 grid gap-5 rounded-xl border p-6 sm:grid-cols-2">
                    <div className="flex gap-3">
                        <CalendarClock className="text-gold-600 size-5 shrink-0" />
                        <div>
                            <dt className="text-muted-foreground text-sm">When</dt>
                            <dd className="font-medium">{formatDateTime(booking.starts_at, tz)}</dd>
                            <dd className="text-muted-foreground text-xs">{tz.replace(/_/g, ' ')}</dd>
                        </div>
                    </div>
                    <div>
                        <dt className="text-muted-foreground text-sm">Fee</dt>
                        <dd className="font-medium">{booking.fee > 0 ? formatMoney(booking.fee, booking.currency) : 'Complimentary'}</dd>
                    </div>
                    {booking.meeting_url && (
                        <div className="flex gap-3 sm:col-span-2">
                            <Video className="text-gold-600 size-5 shrink-0" />
                            <div>
                                <dt className="text-muted-foreground text-sm">Meeting link</dt>
                                <dd>
                                    <a
                                        href={booking.meeting_url}
                                        className="font-medium break-all underline"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        {booking.meeting_url}
                                    </a>
                                </dd>
                            </div>
                        </div>
                    )}
                    {booking.meeting_details && <p className="text-sm whitespace-pre-line sm:col-span-2">{booking.meeting_details}</p>}
                </dl>

                {canPay && (
                    <button
                        onClick={() => router.post(route('bookings.pay', booking.reference), { token })}
                        className="bg-primary text-primary-foreground mt-6 w-full rounded-md px-6 py-3 font-medium"
                    >
                        Pay {formatMoney(booking.fee, booking.currency)} to confirm
                    </button>
                )}

                {(canCancel || canReschedule) && mode === 'view' && (
                    <div className="mt-8 flex flex-wrap gap-3">
                        {canReschedule && (
                            <button
                                onClick={() => setMode('reschedule')}
                                className="border-border hover:border-gold-500 rounded-md border px-5 py-2.5 text-sm font-medium"
                            >
                                Reschedule
                            </button>
                        )}
                        {canCancel && (
                            <button
                                onClick={() => setMode('cancel')}
                                className="text-destructive rounded-md px-5 py-2.5 text-sm font-medium hover:bg-red-50"
                            >
                                Cancel booking
                            </button>
                        )}
                    </div>
                )}

                {mode === 'reschedule' && booking.service_slug && (
                    <form
                        onSubmit={(e) => {
                            e.preventDefault();
                            rescheduleForm.post(route('bookings.reschedule', booking.reference), { onSuccess: () => setMode('view') });
                        }}
                        className="border-border mt-8 space-y-5 rounded-xl border p-6"
                    >
                        <h2 className="text-2xl font-semibold">Choose a new time</h2>
                        {policy.rescheduling && <p className="text-muted-foreground text-sm">{policy.rescheduling}</p>}
                        <SlotPicker
                            serviceSlug={booking.service_slug}
                            timezone={tz}
                            value={rescheduleForm.data.starts_at}
                            onChange={(v) => rescheduleForm.setData('starts_at', v)}
                        />
                        {rescheduleForm.errors.starts_at && <p className="text-destructive text-sm">{rescheduleForm.errors.starts_at}</p>}
                        <div className="flex gap-3">
                            <button
                                type="submit"
                                disabled={!rescheduleForm.data.starts_at || rescheduleForm.processing}
                                className="bg-primary text-primary-foreground rounded-md px-5 py-2.5 text-sm font-medium disabled:opacity-50"
                            >
                                Confirm new time
                            </button>
                            <button type="button" onClick={() => setMode('view')} className="rounded-md px-5 py-2.5 text-sm">
                                Back
                            </button>
                        </div>
                    </form>
                )}

                {mode === 'cancel' && (
                    <form
                        onSubmit={(e) => {
                            e.preventDefault();
                            cancelForm.post(route('bookings.cancel', booking.reference), { onSuccess: () => setMode('view') });
                        }}
                        className="mt-8 space-y-4 rounded-xl border border-red-200 p-6"
                    >
                        <h2 className="text-2xl font-semibold">Cancel this booking?</h2>
                        {policy.cancellation && <p className="text-muted-foreground text-sm">{policy.cancellation}</p>}
                        <label className="block text-sm font-medium" htmlFor="reason">
                            Reason (optional)
                        </label>
                        <textarea
                            id="reason"
                            rows={3}
                            value={cancelForm.data.reason}
                            onChange={(e) => cancelForm.setData('reason', e.target.value)}
                            className="border-input bg-background w-full rounded-md border p-3 text-sm"
                        />
                        <div className="flex gap-3">
                            <button
                                type="submit"
                                disabled={cancelForm.processing}
                                className="bg-destructive rounded-md px-5 py-2.5 text-sm font-medium text-white"
                            >
                                Yes, cancel
                            </button>
                            <button type="button" onClick={() => setMode('view')} className="rounded-md px-5 py-2.5 text-sm">
                                Keep booking
                            </button>
                        </div>
                    </form>
                )}
            </Container>
        </PublicLayout>
    );
}

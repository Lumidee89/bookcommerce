import { formatDate, formatTime } from '@/lib/format';
import { cn } from '@/lib/utils';
import { ChevronLeft, ChevronRight, Loader2 } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';

const WEEKDAYS = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

function monthKey(d: Date) {
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`;
}

async function getJson<T>(url: string): Promise<T> {
    const res = await fetch(url, { headers: { Accept: 'application/json' } });
    if (!res.ok) throw new Error('Request failed');
    return res.json();
}

/**
 * Calendar + time list. Dates come from the owner's calendar (their time zone);
 * times are displayed in the visitor's chosen time zone. The server re-checks the slot on submit.
 */
export function SlotPicker({
    serviceSlug,
    timezone,
    value,
    onChange,
}: {
    serviceSlug: string;
    timezone: string;
    value: string | null;
    onChange: (iso: string | null) => void;
}) {
    const [month, setMonth] = useState(() => new Date(new Date().getFullYear(), new Date().getMonth(), 1));
    const [dates, setDates] = useState<string[] | null>(null);
    const [date, setDate] = useState<string | null>(null);
    const [slots, setSlots] = useState<string[] | null>(null);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        let cancelled = false;
        setDates(null);
        getJson<{ dates: string[] }>(route('consultations.dates', { service: serviceSlug, month: monthKey(month) }))
            .then((r) => !cancelled && setDates(r.dates))
            .catch(() => !cancelled && setError('Could not load availability. Please refresh the page.'));
        return () => {
            cancelled = true;
        };
    }, [serviceSlug, month]);

    useEffect(() => {
        if (!date) return;
        let cancelled = false;
        setSlots(null);
        getJson<{ slots: string[] }>(route('consultations.slots', { service: serviceSlug, date }))
            .then((r) => !cancelled && setSlots(r.slots))
            .catch(() => !cancelled && setError('Could not load times.'));
        return () => {
            cancelled = true;
        };
    }, [serviceSlug, date]);

    const cells = useMemo(() => {
        const first = new Date(month);
        const offset = (first.getDay() + 6) % 7;
        const days = new Date(month.getFullYear(), month.getMonth() + 1, 0).getDate();
        return [...Array(offset).fill(null), ...Array.from({ length: days }, (_, i) => i + 1)];
    }, [month]);

    const thisMonth = new Date(new Date().getFullYear(), new Date().getMonth(), 1);

    return (
        <div className="grid gap-6 md:grid-cols-[1fr_1fr]">
            <div>
                <div className="flex items-center justify-between">
                    <button
                        type="button"
                        onClick={() => setMonth(new Date(month.getFullYear(), month.getMonth() - 1, 1))}
                        disabled={month <= thisMonth}
                        className="hover:bg-accent rounded-md p-2 disabled:opacity-30"
                        aria-label="Previous month"
                    >
                        <ChevronLeft className="size-5" />
                    </button>
                    <p className="font-serif text-xl font-semibold" aria-live="polite">
                        {formatDate(month.toISOString(), { month: 'long', year: 'numeric' })}
                    </p>
                    <button
                        type="button"
                        onClick={() => setMonth(new Date(month.getFullYear(), month.getMonth() + 1, 1))}
                        className="hover:bg-accent rounded-md p-2"
                        aria-label="Next month"
                    >
                        <ChevronRight className="size-5" />
                    </button>
                </div>
                <div className="mt-3 grid grid-cols-7 gap-1 text-center text-xs" role="grid">
                    {WEEKDAYS.map((d) => (
                        <div key={d} className="text-muted-foreground py-1 font-medium" role="columnheader">
                            {d}
                        </div>
                    ))}
                    {cells.map((day, i) => {
                        if (day === null) return <div key={`blank-${i}`} />;
                        const iso = `${monthKey(month)}-${String(day).padStart(2, '0')}`;
                        const available = dates?.includes(iso);
                        return (
                            <button
                                key={iso}
                                type="button"
                                disabled={!available}
                                onClick={() => {
                                    setDate(iso);
                                    onChange(null);
                                }}
                                aria-pressed={date === iso}
                                aria-label={`${formatDate(iso + 'T12:00:00', { weekday: 'long', day: 'numeric', month: 'long' })}${available ? ', available' : ', unavailable'}`}
                                className={cn(
                                    'aspect-square rounded-md text-sm transition-colors',
                                    available
                                        ? 'bg-brand-50 text-brand-900 hover:bg-gold-100 dark:text-foreground font-semibold dark:bg-white/10'
                                        : 'text-muted-foreground/50',
                                    date === iso && 'bg-primary! text-primary-foreground!',
                                )}
                            >
                                {day}
                            </button>
                        );
                    })}
                </div>
                {dates === null && !error && (
                    <p className="text-muted-foreground mt-3 flex items-center gap-2 text-sm">
                        <Loader2 className="size-4 animate-spin" /> Loading availability…
                    </p>
                )}
                {dates?.length === 0 && <p className="text-muted-foreground mt-3 text-sm">No times available this month. Try the next month.</p>}
            </div>

            <div>
                <p className="text-sm font-semibold">
                    {date ? formatDate(date + 'T12:00:00', { weekday: 'long', day: 'numeric', month: 'long' }) : 'Select a date'}
                </p>
                <p className="text-muted-foreground text-xs">Times shown in {timezone.replace(/_/g, ' ')}</p>
                <div className="mt-3 grid max-h-80 grid-cols-2 gap-2 overflow-y-auto pr-1 sm:grid-cols-3 md:grid-cols-2 lg:grid-cols-3">
                    {date && slots === null && <Loader2 className="text-muted-foreground size-5 animate-spin" />}
                    {slots?.map((slot) => {
                        const differentDay = formatDate(slot, { day: 'numeric' }, timezone) !== String(Number(date!.slice(8)));
                        return (
                            <button
                                key={slot}
                                type="button"
                                onClick={() => onChange(slot)}
                                aria-pressed={value === slot}
                                className={cn(
                                    'rounded-md border px-3 py-2.5 text-sm font-medium transition-colors',
                                    value === slot ? 'bg-primary text-primary-foreground border-primary' : 'border-border hover:border-gold-500',
                                )}
                            >
                                {formatTime(slot, timezone)}
                                {differentDay && (
                                    <span className="block text-[0.65rem] opacity-70">
                                        {formatDate(slot, { weekday: 'short', day: 'numeric' }, timezone)}
                                    </span>
                                )}
                            </button>
                        );
                    })}
                    {slots?.length === 0 && <p className="text-muted-foreground col-span-full text-sm">No times left on this day.</p>}
                </div>
            </div>
            {error && (
                <p role="alert" className="text-destructive text-sm md:col-span-2">
                    {error}
                </p>
            )}
        </div>
    );
}

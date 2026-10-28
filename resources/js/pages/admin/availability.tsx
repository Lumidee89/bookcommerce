import { ConfirmAction } from '@/components/admin/confirm-action';
import { Panel } from '@/components/admin/panel';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AdminLayout from '@/layouts/admin-layout';
import { formatDate } from '@/lib/format';
import { useForm } from '@inertiajs/react';
import { Plus, Trash2, X } from 'lucide-react';

const DAYS = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
const ORDER = [1, 2, 3, 4, 5, 6, 0];

type Rule = { day_of_week: number; start_time: string; end_time: string };
type Settings = Record<
    'slot_interval_minutes' | 'buffer_minutes' | 'min_notice_hours' | 'max_advance_days' | 'max_per_day' | 'change_notice_hours' | 'max_reschedules',
    number
>;

const SETTING_LABELS: Record<keyof Settings, [string, string]> = {
    slot_interval_minutes: ['Start times every (minutes)', 'How often appointments may start within working hours.'],
    buffer_minutes: ['Break between appointments (minutes)', 'Preparation / rest time kept free around each booking.'],
    min_notice_hours: ['Minimum notice (hours)', 'How soon a client can book.'],
    max_advance_days: ['Book up to (days ahead)', ''],
    max_per_day: ['Maximum bookings per day', '0 = no limit.'],
    change_notice_hours: ['Online cancel/reschedule cut-off (hours)', 'Clients must change at least this long before the start.'],
    max_reschedules: ['Maximum online reschedules', ''],
};

export default function Availability({
    timezone,
    rules,
    exceptions,
    settings,
}: {
    timezone: string;
    rules: Rule[];
    exceptions: { id: number; date: string; start_time: string | null; end_time: string | null; reason: string | null; delete_url: string }[];
    settings: Settings;
}) {
    const form = useForm<{ rules: Rule[]; settings: Settings }>({ rules, settings });
    const exception = useForm({ date: '', start_time: '', end_time: '', reason: '' });

    const setRule = (index: number, key: keyof Rule, value: string) =>
        form.setData(
            'rules',
            form.data.rules.map((r, i) => (i === index ? { ...r, [key]: value } : r)),
        );

    return (
        <AdminLayout
            title="Availability"
            description={`Working hours in ${timezone}. Clients see times converted to their own time zone.`}
            breadcrumbs={[{ title: 'Availability', href: '/admin/availability' }]}
        >
            <div className="grid gap-6 xl:grid-cols-[1.3fr_1fr]">
                <form
                    onSubmit={(e) => {
                        e.preventDefault();
                        form.put(route('admin.availability.update'), { preserveScroll: true });
                    }}
                    className="space-y-6"
                >
                    <Panel title="Weekly working hours">
                        <ul className="divide-border divide-y">
                            {ORDER.map((day) => {
                                const dayRules = form.data.rules.map((r, i) => ({ ...r, i })).filter((r) => r.day_of_week === day);
                                return (
                                    <li key={day} className="flex flex-wrap items-start gap-3 py-3">
                                        <span className="w-28 pt-2 text-sm font-medium">{DAYS[day]}</span>
                                        <div className="flex flex-1 flex-col gap-2">
                                            {dayRules.length === 0 && <span className="text-muted-foreground pt-2 text-sm">Unavailable</span>}
                                            {dayRules.map((r) => (
                                                <div key={r.i} className="flex items-center gap-2">
                                                    <Input
                                                        type="time"
                                                        aria-label={`${DAYS[day]} start`}
                                                        value={r.start_time}
                                                        onChange={(e) => setRule(r.i, 'start_time', e.target.value)}
                                                        className="w-32"
                                                    />
                                                    <span className="text-muted-foreground">–</span>
                                                    <Input
                                                        type="time"
                                                        aria-label={`${DAYS[day]} end`}
                                                        value={r.end_time}
                                                        onChange={(e) => setRule(r.i, 'end_time', e.target.value)}
                                                        className="w-32"
                                                    />
                                                    <Button
                                                        type="button"
                                                        variant="ghost"
                                                        size="icon"
                                                        aria-label="Remove hours"
                                                        onClick={() =>
                                                            form.setData(
                                                                'rules',
                                                                form.data.rules.filter((_, i) => i !== r.i),
                                                            )
                                                        }
                                                    >
                                                        <X />
                                                    </Button>
                                                </div>
                                            ))}
                                        </div>
                                        <Button
                                            type="button"
                                            variant="outline"
                                            size="sm"
                                            onClick={() =>
                                                form.setData('rules', [
                                                    ...form.data.rules,
                                                    { day_of_week: day, start_time: '09:00', end_time: '17:00' },
                                                ])
                                            }
                                        >
                                            <Plus /> Hours
                                        </Button>
                                    </li>
                                );
                            })}
                        </ul>
                        {Object.entries(form.errors)
                            .filter(([k]) => k.startsWith('rules'))
                            .slice(0, 1)
                            .map(([k, v]) => (
                                <p key={k} className="text-destructive mt-2 text-sm">
                                    {v}
                                </p>
                            ))}
                    </Panel>
                    <Panel title="Booking rules">
                        <div className="grid gap-4 sm:grid-cols-2">
                            {(Object.keys(SETTING_LABELS) as (keyof Settings)[]).map((key) => (
                                <label key={key} className="space-y-1 text-sm">
                                    <span className="font-medium">{SETTING_LABELS[key][0]}</span>
                                    <Input
                                        type="number"
                                        min={0}
                                        value={form.data.settings[key]}
                                        onChange={(e) => form.setData('settings', { ...form.data.settings, [key]: Number(e.target.value) })}
                                    />
                                    {SETTING_LABELS[key][1] && <span className="text-muted-foreground block text-xs">{SETTING_LABELS[key][1]}</span>}
                                    {(form.errors as Record<string, string>)[`settings.${key}`] && (
                                        <span className="text-destructive block text-xs">
                                            {(form.errors as Record<string, string>)[`settings.${key}`]}
                                        </span>
                                    )}
                                </label>
                            ))}
                        </div>
                    </Panel>
                    <Button type="submit" disabled={form.processing}>
                        Save availability
                    </Button>
                </form>

                <Panel
                    title="Blocked dates & special hours"
                    description="Holidays, travel or one-off changes. A date with no times is blocked entirely."
                >
                    <form
                        onSubmit={(e) => {
                            e.preventDefault();
                            exception.post(route('admin.availability.exceptions.store'), {
                                preserveScroll: true,
                                onSuccess: () => exception.reset(),
                            });
                        }}
                        className="grid grid-cols-2 gap-3"
                    >
                        <label className="col-span-2 space-y-1 text-sm">
                            <span className="font-medium">Date</span>
                            <Input type="date" value={exception.data.date} onChange={(e) => exception.setData('date', e.target.value)} />
                            {exception.errors.date && <span className="text-destructive text-xs">{exception.errors.date}</span>}
                        </label>
                        <label className="space-y-1 text-sm">
                            <span className="font-medium">From (optional)</span>
                            <Input type="time" value={exception.data.start_time} onChange={(e) => exception.setData('start_time', e.target.value)} />
                        </label>
                        <label className="space-y-1 text-sm">
                            <span className="font-medium">To (optional)</span>
                            <Input type="time" value={exception.data.end_time} onChange={(e) => exception.setData('end_time', e.target.value)} />
                        </label>
                        {(exception.errors.start_time || exception.errors.end_time) && (
                            <p className="text-destructive col-span-2 text-xs">{exception.errors.start_time || exception.errors.end_time}</p>
                        )}
                        <Input
                            aria-label="Reason"
                            placeholder="Reason (e.g. Travelling)"
                            value={exception.data.reason}
                            onChange={(e) => exception.setData('reason', e.target.value)}
                            className="col-span-2"
                        />
                        <Button
                            type="submit"
                            size="sm"
                            variant="outline"
                            disabled={exception.processing || !exception.data.date}
                            className="col-span-2"
                        >
                            Add date
                        </Button>
                    </form>
                    <ul className="divide-border mt-5 divide-y text-sm">
                        {exceptions.length === 0 && <li className="text-muted-foreground py-2">No upcoming exceptions.</li>}
                        {exceptions.map((ex) => (
                            <li key={ex.id} className="flex items-center gap-3 py-2">
                                <span className="flex-1">
                                    <span className="font-medium">
                                        {formatDate(ex.date + 'T12:00:00', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' })}
                                    </span>
                                    <span className="text-muted-foreground">
                                        {' '}
                                        · {ex.start_time ? `${ex.start_time}–${ex.end_time} only` : 'Blocked'}
                                    </span>
                                    {ex.reason && <span className="text-muted-foreground block text-xs">{ex.reason}</span>}
                                </span>
                                <ConfirmAction
                                    url={ex.delete_url}
                                    title="Remove this date?"
                                    confirmLabel="Remove"
                                    trigger={
                                        <Button variant="ghost" size="icon" aria-label="Remove date">
                                            <Trash2 />
                                        </Button>
                                    }
                                />
                            </li>
                        ))}
                    </ul>
                </Panel>
            </div>
        </AdminLayout>
    );
}

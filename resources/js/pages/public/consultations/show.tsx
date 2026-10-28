import { FormField, Honeypot } from '@/components/site/form-field';
import { Container, Prose } from '@/components/site/section';
import { SlotPicker } from '@/components/site/slot-picker';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import PublicLayout from '@/layouts/public-layout';
import { detectTimeZone, formatDateTime, formatMoney, timeZoneList } from '@/lib/format';
import { type ServiceDetail } from '@/types';
import { Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Clock, MonitorSmartphone, ShieldCheck } from 'lucide-react';
import { FormEventHandler, useMemo } from 'react';

export default function ConsultationShow({
    service,
    bookable,
    policyUrl,
}: {
    service: ServiceDetail;
    ownerTimezone: string;
    bookable: boolean;
    policyUrl: string | null;
}) {
    const zones = useMemo(timeZoneList, []);
    const { data, setData, post, processing, errors } = useForm({
        starts_at: null as string | null,
        timezone: detectTimeZone(),
        name: '',
        email: '',
        phone: '',
        organisation: '',
        needs: '',
        accept_policy: false as boolean,
        website: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('consultations.book', service.slug), { preserveScroll: true });
    };

    return (
        <PublicLayout>
            <Container className="py-10">
                <Link
                    href={route('consultations.index')}
                    className="text-muted-foreground hover:text-foreground inline-flex items-center gap-1 text-sm"
                >
                    <ArrowLeft className="size-4" /> All consultations
                </Link>
                <div className="mt-6 grid gap-12 lg:grid-cols-[1fr_1.35fr]">
                    <div>
                        <h1 className="text-brand-900 dark:text-foreground text-5xl leading-tight font-semibold">{service.name}</h1>
                        <div className="mt-5 flex flex-wrap gap-x-5 gap-y-2 text-sm">
                            <span className="inline-flex items-center gap-1.5">
                                <Clock className="text-gold-600 size-4" /> {service.duration_minutes} minutes
                            </span>
                            <span className="inline-flex items-center gap-1.5">
                                <MonitorSmartphone className="text-gold-600 size-4" /> {service.delivery_method_label}
                            </span>
                            <span className="font-semibold">{service.fee > 0 ? formatMoney(service.fee, service.currency) : 'Complimentary'}</span>
                        </div>
                        {service.summary && <p className="text-muted-foreground mt-6 text-lg">{service.summary}</p>}
                        <Prose html={service.description_html} className="mt-6" />
                        {service.audience && (
                            <p className="mt-6">
                                <span className="font-semibold">Who it's for:</span> {service.audience}
                            </p>
                        )}
                        {service.preparation_instructions && (
                            <div className="bg-muted/60 mt-8 rounded-xl p-5">
                                <h2 className="font-sans text-sm font-semibold">How to prepare</h2>
                                <p className="text-muted-foreground mt-1 text-sm whitespace-pre-line">{service.preparation_instructions}</p>
                            </div>
                        )}
                        {(service.cancellation_policy || service.rescheduling_policy) && (
                            <div className="border-border mt-6 space-y-3 rounded-xl border p-5 text-sm">
                                {service.cancellation_policy && (
                                    <p>
                                        <span className="font-semibold">Cancellation: </span>
                                        {service.cancellation_policy}
                                    </p>
                                )}
                                {service.rescheduling_policy && (
                                    <p>
                                        <span className="font-semibold">Rescheduling: </span>
                                        {service.rescheduling_policy}
                                    </p>
                                )}
                                {policyUrl && (
                                    <a href={policyUrl} className="underline">
                                        Full booking policy
                                    </a>
                                )}
                            </div>
                        )}
                    </div>

                    <div className="border-border bg-card rounded-2xl border p-6 shadow-sm sm:p-8">
                        {bookable ? (
                            <form onSubmit={submit} className="space-y-8" noValidate>
                                <Honeypot value={data.website} onChange={(v) => setData('website', v)} />
                                <section aria-labelledby="step-time">
                                    <div className="flex flex-wrap items-end justify-between gap-3">
                                        <h2 id="step-time" className="text-2xl font-semibold">
                                            1. Choose a time
                                        </h2>
                                        <label className="text-sm">
                                            <span className="text-muted-foreground mr-2">Your time zone</span>
                                            <select
                                                value={data.timezone}
                                                onChange={(e) => setData('timezone', e.target.value)}
                                                className="border-input bg-background max-w-52 rounded-md border px-2 py-1.5 text-sm"
                                            >
                                                {zones.map((z) => (
                                                    <option key={z} value={z}>
                                                        {z.replace(/_/g, ' ')}
                                                    </option>
                                                ))}
                                            </select>
                                        </label>
                                    </div>
                                    <div className="mt-5">
                                        <SlotPicker
                                            serviceSlug={service.slug}
                                            timezone={data.timezone}
                                            value={data.starts_at}
                                            onChange={(v) => setData('starts_at', v)}
                                        />
                                    </div>
                                    {errors.starts_at && (
                                        <p className="text-destructive mt-3 text-sm" role="alert">
                                            {errors.starts_at}
                                        </p>
                                    )}
                                </section>

                                <section aria-labelledby="step-details" className="space-y-4">
                                    <h2 id="step-details" className="text-2xl font-semibold">
                                        2. Your details
                                    </h2>
                                    <div className="grid gap-4 sm:grid-cols-2">
                                        <FormField id="name" label="Full name" required error={errors.name}>
                                            <Input
                                                id="name"
                                                value={data.name}
                                                onChange={(e) => setData('name', e.target.value)}
                                                autoComplete="name"
                                                required
                                            />
                                        </FormField>
                                        <FormField id="email" label="Email" required error={errors.email}>
                                            <Input
                                                id="email"
                                                type="email"
                                                value={data.email}
                                                onChange={(e) => setData('email', e.target.value)}
                                                autoComplete="email"
                                                required
                                            />
                                        </FormField>
                                        <FormField id="phone" label="Telephone" error={errors.phone}>
                                            <Input
                                                id="phone"
                                                type="tel"
                                                value={data.phone}
                                                onChange={(e) => setData('phone', e.target.value)}
                                                autoComplete="tel"
                                            />
                                        </FormField>
                                        <FormField id="organisation" label="Organisation" error={errors.organisation}>
                                            <Input
                                                id="organisation"
                                                value={data.organisation}
                                                onChange={(e) => setData('organisation', e.target.value)}
                                                autoComplete="organization"
                                            />
                                        </FormField>
                                    </div>
                                    <FormField
                                        id="needs"
                                        label="What would you like to discuss?"
                                        required
                                        error={errors.needs}
                                        hint="A short summary helps the session start well. This is kept confidential."
                                    >
                                        <Textarea
                                            id="needs"
                                            rows={5}
                                            value={data.needs}
                                            onChange={(e) => setData('needs', e.target.value)}
                                            required
                                        />
                                    </FormField>
                                </section>

                                <section aria-labelledby="step-review" className="bg-muted/50 space-y-4 rounded-xl p-5">
                                    <h2 id="step-review" className="text-2xl font-semibold">
                                        3. Review
                                    </h2>
                                    <dl className="grid gap-2 text-sm sm:grid-cols-2">
                                        <div>
                                            <dt className="text-muted-foreground">Service</dt>
                                            <dd className="font-medium">{service.name}</dd>
                                        </div>
                                        <div>
                                            <dt className="text-muted-foreground">Time</dt>
                                            <dd className="font-medium">
                                                {data.starts_at ? formatDateTime(data.starts_at, data.timezone) : 'Not selected'}
                                            </dd>
                                        </div>
                                        <div>
                                            <dt className="text-muted-foreground">Fee</dt>
                                            <dd className="font-medium">
                                                {service.fee > 0 ? formatMoney(service.fee, service.currency) : 'Complimentary'}
                                            </dd>
                                        </div>
                                    </dl>
                                    <label className="flex items-start gap-2 text-sm">
                                        <input
                                            type="checkbox"
                                            className="accent-gold-600 mt-0.5 size-4"
                                            checked={data.accept_policy}
                                            onChange={(e) => setData('accept_policy', e.target.checked)}
                                        />
                                        <span>I have read the cancellation and rescheduling policy.</span>
                                    </label>
                                    {errors.accept_policy && <p className="text-destructive text-sm">{errors.accept_policy}</p>}
                                    <button
                                        type="submit"
                                        disabled={processing || !data.starts_at}
                                        className="bg-primary text-primary-foreground hover:bg-primary/90 w-full rounded-md px-6 py-3 font-medium disabled:opacity-50"
                                    >
                                        {processing
                                            ? 'Reserving…'
                                            : service.fee > 0
                                              ? `Reserve & pay ${formatMoney(service.fee, service.currency)}`
                                              : 'Confirm booking'}
                                    </button>
                                    {service.fee > 0 && (
                                        <p className="text-muted-foreground flex items-start gap-1.5 text-xs">
                                            <ShieldCheck className="mt-0.5 size-3.5 shrink-0" /> Your slot is held briefly while you pay. The booking
                                            is confirmed only once payment is verified.
                                        </p>
                                    )}
                                </section>
                            </form>
                        ) : (
                            <div className="py-10 text-center">
                                <h2 className="text-2xl font-semibold">Online booking for this service is not yet open</h2>
                                <p className="text-muted-foreground mx-auto mt-3 max-w-md">
                                    Online payment is still being set up. Please send an enquiry and we will arrange a time with you.
                                </p>
                                <Link
                                    href={route('contact')}
                                    className="bg-primary text-primary-foreground mt-6 inline-flex rounded-md px-5 py-2.5 text-sm font-medium"
                                >
                                    Send an enquiry
                                </Link>
                            </div>
                        )}
                    </div>
                </div>
            </Container>
        </PublicLayout>
    );
}

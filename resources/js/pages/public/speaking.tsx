import { FormField, Honeypot } from '@/components/site/form-field';
import { Container, PageHero } from '@/components/site/section';
import { Input } from '@/components/ui/input';
import { NativeSelect } from '@/components/ui/native-select';
import { Textarea } from '@/components/ui/textarea';
import PublicLayout from '@/layouts/public-layout';
import { track } from '@/lib/analytics';
import { detectTimeZone, timeZoneList } from '@/lib/format';
import { useForm } from '@inertiajs/react';
import { Info } from 'lucide-react';
import { FormEventHandler, useMemo } from 'react';

const initial = {
    contact_name: '',
    organisation: '',
    email: '',
    phone: '',
    event_name: '',
    event_purpose: '',
    proposed_date: '',
    timezone: '',
    venue: '',
    country: '',
    location: '',
    audience_type: '',
    audience_size: '',
    requested_topic: '',
    speaking_duration: '',
    budget: '',
    travel_arrangements: '',
    additional_information: '',
    website: '',
};

export default function Speaking() {
    const zones = useMemo(timeZoneList, []);
    const { data, setData, post, processing, errors, reset, wasSuccessful } = useForm({ ...initial, timezone: detectTimeZone() });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('speaking.store'), {
            preserveScroll: true,
            onSuccess: () => {
                track('generate_lead', { lead_type: 'speaking_invitation' });
                reset();
            },
        });
    };

    const field = (
        name: keyof typeof initial,
        label: string,
        opts: { required?: boolean; type?: string; textarea?: boolean; hint?: string; className?: string } = {},
    ) => (
        <FormField id={name} label={label} required={opts.required} error={errors[name]} hint={opts.hint} className={opts.className}>
            {opts.textarea ? (
                <Textarea id={name} rows={4} value={data[name]} onChange={(e) => setData(name, e.target.value)} />
            ) : (
                <Input
                    id={name}
                    type={opts.type ?? 'text'}
                    value={data[name]}
                    onChange={(e) => setData(name, e.target.value)}
                    required={opts.required}
                />
            )}
        </FormField>
    );

    return (
        <PublicLayout>
            <PageHero
                eyebrow="Speaking & training"
                title="Invite Yemi Adebanjo to speak"
                description="Conferences, leadership trainings, church services, retreats and institutional events — in person or online."
            />
            <Container className="py-14">
                <div className="bg-gold-50 border-gold-200 text-gold-900 dark:text-gold-100 mb-8 flex items-start gap-3 rounded-xl border p-5 text-sm dark:border-white/10 dark:bg-white/5">
                    <Info className="mt-0.5 size-5 shrink-0" />
                    <p>
                        Submitting an invitation does not confirm availability or acceptance. Every invitation is reviewed, and you will receive a
                        response by email.
                    </p>
                </div>
                <form onSubmit={submit} className="border-border bg-card relative space-y-10 rounded-2xl border p-6 sm:p-8" noValidate>
                    <Honeypot value={data.website} onChange={(v) => setData('website', v)} />
                    {wasSuccessful && (
                        <p role="status" className="bg-brand-50 text-brand-900 rounded-md p-4 text-sm">
                            Thank you — your invitation has been received. An acknowledgement has been emailed to you.
                        </p>
                    )}
                    <fieldset className="grid gap-5 sm:grid-cols-2">
                        <legend className="mb-4 text-2xl font-semibold">Contact</legend>
                        {field('contact_name', 'Contact person', { required: true })}
                        {field('organisation', 'Organisation', { required: true })}
                        {field('email', 'Email', { required: true, type: 'email' })}
                        {field('phone', 'Telephone', { type: 'tel' })}
                    </fieldset>
                    <fieldset className="grid gap-5 sm:grid-cols-2">
                        <legend className="mb-4 text-2xl font-semibold">Event</legend>
                        {field('event_name', 'Event name', { required: true })}
                        {field('requested_topic', 'Requested topic')}
                        {field('event_purpose', 'Purpose of the event', { textarea: true, className: 'sm:col-span-2' })}
                        {field('proposed_date', 'Proposed date', { type: 'date' })}
                        <FormField id="timezone" label="Time zone" error={errors.timezone}>
                            <NativeSelect id="timezone" value={data.timezone} onChange={(e) => setData('timezone', e.target.value)}>
                                {zones.map((z) => (
                                    <option key={z} value={z}>
                                        {z.replace(/_/g, ' ')}
                                    </option>
                                ))}
                            </NativeSelect>
                        </FormField>
                        {field('venue', 'Venue or online platform')}
                        {field('location', 'City / location')}
                        {field('country', 'Country')}
                        {field('speaking_duration', 'Expected speaking duration', { hint: 'e.g. 45 minutes, two sessions' })}
                    </fieldset>
                    <fieldset className="grid gap-5 sm:grid-cols-2">
                        <legend className="mb-4 text-2xl font-semibold">Audience & arrangements</legend>
                        {field('audience_type', 'Audience type', { hint: 'e.g. church leaders, executives, students' })}
                        {field('audience_size', 'Estimated audience size')}
                        {field('budget', 'Budget or honorarium (if applicable)')}
                        {field('travel_arrangements', 'Travel and accommodation arrangements', { textarea: true })}
                        {field('additional_information', 'Anything else we should know?', { textarea: true, className: 'sm:col-span-2' })}
                    </fieldset>
                    {errors.website && <p className="text-destructive text-sm">{errors.website}</p>}
                    <button
                        type="submit"
                        disabled={processing}
                        className="bg-primary text-primary-foreground rounded-md px-6 py-3 font-medium disabled:opacity-60"
                    >
                        {processing ? 'Sending…' : 'Submit invitation'}
                    </button>
                </form>
            </Container>
        </PublicLayout>
    );
}

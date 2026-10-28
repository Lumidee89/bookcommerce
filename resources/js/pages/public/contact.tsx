import { FormField, Honeypot } from '@/components/site/form-field';
import { Container, PageHero } from '@/components/site/section';
import { Input } from '@/components/ui/input';
import { NativeSelect } from '@/components/ui/native-select';
import { Textarea } from '@/components/ui/textarea';
import PublicLayout from '@/layouts/public-layout';
import { track } from '@/lib/analytics';
import { type SharedData } from '@/types';
import { Link, useForm, usePage } from '@inertiajs/react';
import { CalendarCheck, Mail, Mic2, Phone } from 'lucide-react';
import { FormEventHandler } from 'react';

export default function Contact({ categories }: { categories: { value: string; label: string }[] }) {
    const { site } = usePage<SharedData>().props;
    const { data, setData, post, processing, errors, reset, wasSuccessful } = useForm({
        category: 'general',
        name: '',
        email: '',
        phone: '',
        subject: '',
        message: '',
        website: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('contact.store'), {
            preserveScroll: true,
            onSuccess: () => {
                track('generate_lead', { lead_type: 'contact', category: data.category });
                reset();
            },
        });
    };

    return (
        <PublicLayout>
            <PageHero
                eyebrow="Contact"
                title="Get in touch"
                description="For books, media, partnerships or general questions. Messages are read by the team and answered as soon as possible."
            />
            <Container className="grid gap-12 py-14 lg:grid-cols-[1fr_2fr]">
                <aside className="space-y-6">
                    {site.contact.email && (
                        <a href={`mailto:${site.contact.email}`} className="flex gap-3">
                            <Mail className="text-gold-600 size-5" />
                            <span className="break-all">{site.contact.email}</span>
                        </a>
                    )}
                    {site.contact.phone && (
                        <p className="flex gap-3">
                            <Phone className="text-gold-600 size-5" />
                            {site.contact.phone}
                        </p>
                    )}
                    <div className="border-border space-y-3 border-t pt-6">
                        <Link href={route('consultations.index')} className="hover:bg-accent flex items-start gap-3 rounded-lg p-3">
                            <CalendarCheck className="text-gold-600 mt-0.5 size-5 shrink-0" />
                            <span>
                                <span className="font-semibold">Booking a consultation?</span>
                                <span className="text-muted-foreground block text-sm">Use the booking page to choose a time directly.</span>
                            </span>
                        </Link>
                        <Link href={route('speaking')} className="hover:bg-accent flex items-start gap-3 rounded-lg p-3">
                            <Mic2 className="text-gold-600 mt-0.5 size-5 shrink-0" />
                            <span>
                                <span className="font-semibold">Inviting Yemi to speak?</span>
                                <span className="text-muted-foreground block text-sm">
                                    The speaking form collects everything needed to review your event.
                                </span>
                            </span>
                        </Link>
                    </div>
                </aside>

                <form onSubmit={submit} className="border-border bg-card relative space-y-5 rounded-2xl border p-6 sm:p-8" noValidate>
                    <Honeypot value={data.website} onChange={(v) => setData('website', v)} />
                    {wasSuccessful && (
                        <p role="status" className="bg-brand-50 text-brand-900 rounded-md p-4 text-sm">
                            Thank you — your message has been sent. A confirmation is on its way to your inbox.
                        </p>
                    )}
                    <div className="grid gap-5 sm:grid-cols-2">
                        <FormField id="category" label="Enquiry about" required error={errors.category}>
                            <NativeSelect id="category" value={data.category} onChange={(e) => setData('category', e.target.value)}>
                                {categories.map((c) => (
                                    <option key={c.value} value={c.value}>
                                        {c.label}
                                    </option>
                                ))}
                            </NativeSelect>
                        </FormField>
                        <FormField id="subject" label="Subject" required error={errors.subject}>
                            <Input id="subject" value={data.subject} onChange={(e) => setData('subject', e.target.value)} required />
                        </FormField>
                        <FormField id="name" label="Your name" required error={errors.name}>
                            <Input id="name" value={data.name} onChange={(e) => setData('name', e.target.value)} autoComplete="name" required />
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
                            <Input id="phone" type="tel" value={data.phone} onChange={(e) => setData('phone', e.target.value)} autoComplete="tel" />
                        </FormField>
                    </div>
                    <FormField id="message" label="Message" required error={errors.message}>
                        <Textarea id="message" rows={7} value={data.message} onChange={(e) => setData('message', e.target.value)} required />
                    </FormField>
                    {errors.website && <p className="text-destructive text-sm">{errors.website}</p>}
                    <p className="text-muted-foreground text-xs">
                        Sending this form does not subscribe you to any mailing list. See our{' '}
                        <a href="/pages/privacy-policy" className="underline">
                            privacy policy
                        </a>
                        .
                    </p>
                    <button
                        type="submit"
                        disabled={processing}
                        className="bg-primary text-primary-foreground rounded-md px-6 py-3 font-medium disabled:opacity-60"
                    >
                        {processing ? 'Sending…' : 'Send message'}
                    </button>
                </form>
            </Container>
        </PublicLayout>
    );
}

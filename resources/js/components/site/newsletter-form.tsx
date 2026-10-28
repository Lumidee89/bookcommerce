import { Honeypot } from '@/components/site/form-field';
import { track } from '@/lib/analytics';
import { cn } from '@/lib/utils';
import { type SharedData } from '@/types';
import { useForm, usePage } from '@inertiajs/react';
import { FormEventHandler, useState } from 'react';

export function NewsletterForm({ tone = 'dark' }: { tone?: 'dark' | 'light' }) {
    const { newsletterInterests } = usePage<SharedData & { newsletterInterests: { id: number; name: string }[] }>().props;
    const [done, setDone] = useState(false);
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        name: '',
        interests: [] as number[],
        consent: false as boolean,
        website: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('newsletter.store'), {
            preserveScroll: true,
            onSuccess: () => {
                track('sign_up', { method: 'newsletter' });
                reset();
                setDone(true);
            },
        });
    };

    const dark = tone === 'dark';
    const inputClass = cn(
        'focus-visible:ring-gold-400 h-11 w-full rounded-md border px-3 text-sm focus-visible:ring-2 focus-visible:outline-none',
        dark ? 'border-white/20 bg-white/10 text-white placeholder:text-white/60' : 'border-input bg-background',
    );

    if (done) {
        return (
            <p role="status" className={cn('rounded-md p-4 text-sm', dark ? 'bg-white/10 text-white' : 'bg-accent')}>
                Thank you. Please check your inbox and click the confirmation link to complete your subscription.
            </p>
        );
    }

    return (
        <form onSubmit={submit} className="space-y-3" noValidate>
            <Honeypot value={data.website} onChange={(v) => setData('website', v)} />
            <div className="flex flex-col gap-3 sm:flex-row">
                <label className="sr-only" htmlFor="nl-name">
                    Name
                </label>
                <input
                    id="nl-name"
                    className={cn(inputClass, 'sm:max-w-44')}
                    placeholder="First name"
                    value={data.name}
                    onChange={(e) => setData('name', e.target.value)}
                    autoComplete="given-name"
                />
                <label className="sr-only" htmlFor="nl-email">
                    Email address
                </label>
                <input
                    id="nl-email"
                    type="email"
                    required
                    className={inputClass}
                    placeholder="Email address"
                    value={data.email}
                    onChange={(e) => setData('email', e.target.value)}
                    autoComplete="email"
                    aria-invalid={!!errors.email}
                    aria-describedby={errors.email ? 'nl-email-error' : undefined}
                />
                <button
                    type="submit"
                    disabled={processing}
                    className="bg-gold-600 hover:bg-gold-700 h-11 shrink-0 rounded-md px-5 text-sm font-semibold text-white transition-colors disabled:opacity-60"
                >
                    Subscribe
                </button>
            </div>
            {errors.email && (
                <p id="nl-email-error" className={cn('text-sm', dark ? 'text-red-200' : 'text-destructive')}>
                    {errors.email}
                </p>
            )}
            {newsletterInterests?.length > 0 && (
                <fieldset className="flex flex-wrap gap-x-5 gap-y-2">
                    <legend className={cn('mb-1 text-xs', dark ? 'text-white/70' : 'text-muted-foreground')}>I'm interested in (optional):</legend>
                    {newsletterInterests.map((interest) => (
                        <label key={interest.id} className={cn('flex items-center gap-2 text-sm', dark ? 'text-white/90' : '')}>
                            <input
                                type="checkbox"
                                className="accent-gold-600 size-4"
                                checked={data.interests.includes(interest.id)}
                                onChange={(e) =>
                                    setData(
                                        'interests',
                                        e.target.checked ? [...data.interests, interest.id] : data.interests.filter((i) => i !== interest.id),
                                    )
                                }
                            />
                            {interest.name}
                        </label>
                    ))}
                </fieldset>
            )}
            <label className={cn('flex items-start gap-2 text-sm', dark ? 'text-white/85' : 'text-muted-foreground')}>
                <input
                    type="checkbox"
                    className="accent-gold-600 mt-0.5 size-4 shrink-0"
                    checked={data.consent}
                    onChange={(e) => setData('consent', e.target.checked)}
                />
                <span>I agree to receive occasional emails about new books, articles, teachings and events. I can unsubscribe at any time.</span>
            </label>
            {errors.consent && <p className={cn('text-sm', dark ? 'text-red-200' : 'text-destructive')}>{errors.consent}</p>}
        </form>
    );
}

export function NewsletterBand() {
    return (
        <section aria-labelledby="newsletter-heading" className="bg-brand-900 relative overflow-hidden text-white">
            <img
                src="/images/brand/monogram-light.png"
                alt=""
                aria-hidden
                className="pointer-events-none absolute -top-10 -right-16 hidden w-96 opacity-[0.06] md:block"
            />
            <div className="relative mx-auto grid max-w-7xl gap-10 px-4 py-16 sm:px-6 lg:grid-cols-2 lg:px-8">
                <div>
                    <p className="text-gold-300 text-xs font-semibold tracking-[0.25em] uppercase">Stay connected</p>
                    <h2 id="newsletter-heading" className="mt-3 text-4xl font-semibold text-[#f7f3ea]">
                        Teachings, new books and events — in your inbox.
                    </h2>
                    <p className="mt-4 max-w-lg text-white/75">Occasional, thoughtful updates. No spam, and you can unsubscribe at any time.</p>
                </div>
                <div className="lg:pt-6">
                    <NewsletterForm />
                </div>
            </div>
        </section>
    );
}

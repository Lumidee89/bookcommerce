import { Container, EmptyState, PageHero } from '@/components/site/section';
import PublicLayout from '@/layouts/public-layout';
import { formatMoney } from '@/lib/format';
import { type ServiceCard } from '@/types';
import { Link } from '@inertiajs/react';
import { ArrowRight, CalendarCheck, Clock, Mail, MonitorSmartphone } from 'lucide-react';

export default function ConsultationsIndex({ services }: { services: ServiceCard[] }) {
    return (
        <PublicLayout>
            <PageHero
                eyebrow="Consultations"
                title="Book a consultation"
                description="Focused, confidential sessions for leaders, business owners, ministries and authors. Choose a service and a time that suits you."
            />
            <Container className="py-14">
                <ol className="mb-14 grid gap-4 sm:grid-cols-3">
                    {[
                        { icon: CalendarCheck, title: 'Choose a service & time', text: 'Live availability is shown in your own time zone.' },
                        {
                            icon: MonitorSmartphone,
                            title: 'Share your needs',
                            text: 'Tell us what you would like to discuss so the session is well prepared.',
                        },
                        {
                            icon: Mail,
                            title: 'Receive confirmation',
                            text: 'A confirmation, calendar invite and meeting details are emailed to you.',
                        },
                    ].map((step, i) => (
                        <li key={step.title} className="border-border bg-card flex gap-4 rounded-xl border p-5">
                            <span className="bg-gold-100 text-gold-800 flex size-9 shrink-0 items-center justify-center rounded-full font-serif text-lg font-semibold">
                                {i + 1}
                            </span>
                            <div>
                                <p className="font-semibold">{step.title}</p>
                                <p className="text-muted-foreground text-sm">{step.text}</p>
                            </div>
                        </li>
                    ))}
                </ol>

                {services.length > 0 ? (
                    <div className="grid gap-6 md:grid-cols-2">
                        {services.map((s) => (
                            <Link
                                key={s.id}
                                href={route('consultations.show', s.slug)}
                                className="group border-border bg-card hover:border-gold-500 flex flex-col rounded-xl border p-7 transition-colors"
                            >
                                <h2 className="group-hover:text-gold-700 text-3xl leading-tight font-semibold">{s.name}</h2>
                                {s.summary && <p className="text-muted-foreground mt-3 flex-1">{s.summary}</p>}
                                {s.audience && (
                                    <p className="mt-3 text-sm">
                                        <span className="text-muted-foreground">For:</span> {s.audience}
                                    </p>
                                )}
                                <div className="border-border mt-6 flex flex-wrap items-center gap-x-5 gap-y-2 border-t pt-5 text-sm">
                                    <span className="inline-flex items-center gap-1.5">
                                        <Clock className="text-gold-600 size-4" /> {s.duration_minutes} minutes
                                    </span>
                                    <span className="inline-flex items-center gap-1.5">
                                        <MonitorSmartphone className="text-gold-600 size-4" /> {s.delivery_method_label}
                                    </span>
                                    <span className="text-brand-900 dark:text-gold-200 ml-auto font-semibold">
                                        {s.fee > 0 ? formatMoney(s.fee, s.currency) : 'Complimentary'}
                                    </span>
                                </div>
                                <span className="text-gold-700 dark:text-gold-300 mt-5 inline-flex items-center gap-1.5 text-sm font-semibold">
                                    Check availability <ArrowRight className="size-4" />
                                </span>
                            </Link>
                        ))}
                    </div>
                ) : (
                    <EmptyState
                        title="Consultation booking opens soon"
                        description="Services are being finalised. In the meantime, please get in touch."
                    >
                        <Link href={route('contact')} className="bg-primary text-primary-foreground rounded-md px-5 py-2.5 text-sm font-medium">
                            Contact us
                        </Link>
                    </EmptyState>
                )}
            </Container>
        </PublicLayout>
    );
}

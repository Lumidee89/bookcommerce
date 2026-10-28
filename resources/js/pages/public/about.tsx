import { Container, PageHero, Prose, SectionHeading } from '@/components/site/section';
import PublicLayout from '@/layouts/public-layout';
import { formatDate } from '@/lib/format';
import { type AwardItem, type TestimonialItem } from '@/types';
import { Link } from '@inertiajs/react';
import { Award, Download, ExternalLink, Quote } from 'lucide-react';

interface Props {
    bioShort: string | null;
    sections: { id: number; title: string; slug: string; body_html: string; image_url: string | null; image_alt: string | null }[];
    awardGroups: { label: string; items: AwardItem[] }[];
    documents: { id: number; title: string; description: string | null; url: string }[];
    testimonials: TestimonialItem[];
}

export default function About({ bioShort, sections, awardGroups, documents, testimonials }: Props) {
    return (
        <PublicLayout>
            <PageHero eyebrow="About" title="Yemi Adebanjo" description={bioShort}>
                <div className="mt-8 flex flex-wrap gap-3">
                    <Link
                        href={route('consultations.index')}
                        className="bg-primary text-primary-foreground rounded-md px-5 py-2.5 text-sm font-medium"
                    >
                        Book a consultation
                    </Link>
                    <Link href={route('speaking')} className="border-border hover:border-gold-600 rounded-md border px-5 py-2.5 text-sm font-medium">
                        Speaking invitations
                    </Link>
                    {documents[0] && (
                        <a
                            href={documents[0].url}
                            className="text-gold-700 dark:text-gold-300 inline-flex items-center gap-1.5 px-2 py-2.5 text-sm font-semibold"
                            download
                        >
                            <Download className="size-4" /> {documents[0].title}
                        </a>
                    )}
                </div>
            </PageHero>

            {sections.length > 0 ? (
                <Container className="py-16">
                    {sections.length > 2 && (
                        <nav aria-label="On this page" className="border-border mb-12 flex flex-wrap gap-2 border-b pb-6">
                            {sections.map((s) => (
                                <a
                                    key={s.id}
                                    href={`#${s.slug}`}
                                    className="border-border hover:border-gold-500 rounded-full border px-4 py-1.5 text-sm"
                                >
                                    {s.title}
                                </a>
                            ))}
                        </nav>
                    )}
                    <div className="space-y-20">
                        {sections.map((section, i) => (
                            <section
                                key={section.id}
                                id={section.slug}
                                aria-labelledby={`${section.slug}-heading`}
                                className="grid scroll-mt-28 gap-10 lg:grid-cols-[1fr_2fr]"
                            >
                                <div className={i % 2 === 1 ? 'lg:order-2' : ''}>
                                    <h2 id={`${section.slug}-heading`} className="text-brand-900 dark:text-foreground text-4xl font-semibold">
                                        {section.title}
                                    </h2>
                                    <div className="gold-rule mt-4" />
                                    {section.image_url && (
                                        <img
                                            src={section.image_url}
                                            alt={section.image_alt ?? ''}
                                            loading="lazy"
                                            className="mt-8 w-full rounded-xl object-cover"
                                        />
                                    )}
                                </div>
                                <Prose html={section.body_html} />
                            </section>
                        ))}
                    </div>
                </Container>
            ) : (
                <Container className="py-16">
                    <p className="text-muted-foreground">The full biography will be published here once approved.</p>
                </Container>
            )}

            {awardGroups.length > 0 && (
                <section id="recognition" aria-labelledby="recognition-heading" className="bg-muted/40 border-border scroll-mt-24 border-y py-20">
                    <Container>
                        <SectionHeading id="recognition-heading" eyebrow="Recognition" title="Awards, qualifications & memberships" />
                        <div className="space-y-12">
                            {awardGroups.map((group) => (
                                <div key={group.label}>
                                    <h3 className="text-gold-700 dark:text-gold-300 font-sans text-sm font-semibold tracking-widest uppercase">
                                        {group.label}
                                    </h3>
                                    <ul className="mt-5 grid gap-5 md:grid-cols-2">
                                        {group.items.map((award) => (
                                            <li key={award.id} className="bg-card border-border flex gap-4 rounded-xl border p-5">
                                                {award.image_url ? (
                                                    <img src={award.image_url} alt="" className="size-16 shrink-0 rounded-md object-cover" />
                                                ) : (
                                                    <Award className="text-gold-600 size-8 shrink-0" strokeWidth={1.5} />
                                                )}
                                                <div>
                                                    <p className="text-lg leading-snug font-semibold">{award.title}</p>
                                                    <p className="text-muted-foreground text-sm">
                                                        {[award.issuer, award.awarded_on && formatDate(award.awarded_on, { year: 'numeric' })]
                                                            .filter(Boolean)
                                                            .join(' · ')}
                                                    </p>
                                                    {award.description && <p className="mt-2 text-sm">{award.description}</p>}
                                                    <div className="mt-2 flex gap-4 text-sm">
                                                        {award.certificate_url && (
                                                            <a
                                                                href={award.certificate_url}
                                                                target="_blank"
                                                                rel="noopener"
                                                                className="text-gold-700 dark:text-gold-300 underline-offset-4 hover:underline"
                                                            >
                                                                View certificate
                                                            </a>
                                                        )}
                                                        {award.url && (
                                                            <a
                                                                href={award.url}
                                                                target="_blank"
                                                                rel="noopener noreferrer"
                                                                className="text-gold-700 dark:text-gold-300 inline-flex items-center gap-1 underline-offset-4 hover:underline"
                                                            >
                                                                More <ExternalLink className="size-3" />
                                                            </a>
                                                        )}
                                                    </div>
                                                </div>
                                            </li>
                                        ))}
                                    </ul>
                                </div>
                            ))}
                        </div>
                    </Container>
                </section>
            )}

            {(documents.length > 0 || testimonials.length > 0) && (
                <Container className="grid gap-12 py-20 lg:grid-cols-[1fr_2fr]">
                    {documents.length > 0 && (
                        <div>
                            <h2 className="text-3xl font-semibold">Media kit</h2>
                            <ul className="mt-6 space-y-3">
                                {documents.map((doc) => (
                                    <li key={doc.id}>
                                        <a
                                            href={doc.url}
                                            download
                                            className="border-border hover:border-gold-500 flex items-start gap-3 rounded-lg border p-4"
                                        >
                                            <Download className="text-gold-600 mt-0.5 size-5 shrink-0" />
                                            <span>
                                                <span className="font-medium">{doc.title}</span>
                                                {doc.description && <span className="text-muted-foreground block text-sm">{doc.description}</span>}
                                            </span>
                                        </a>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    )}
                    {testimonials.length > 0 && (
                        <div className="grid gap-5 sm:grid-cols-2">
                            {testimonials.map((t) => (
                                <figure key={t.id} className="bg-card border-border rounded-xl border p-6">
                                    <Quote className="text-gold-500 size-6" />
                                    <blockquote className="mt-3 font-serif text-lg">“{t.body}”</blockquote>
                                    <figcaption className="text-muted-foreground mt-4 text-sm">
                                        <span className="text-foreground font-semibold">{t.name}</span>
                                        {t.organisation && `, ${t.organisation}`}
                                    </figcaption>
                                </figure>
                            ))}
                        </div>
                    )}
                </Container>
            )}
        </PublicLayout>
    );
}
